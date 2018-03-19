<?php

namespace App\Manager;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class MriqManager
{
    /**
     * @var SlackManager
     */
    private $slackManager;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var int
     */
    private $maxTransactionAmount;

    /**
     * MriqManager constructor.
     * @param SlackManager $slackManager
     */
    public function __construct(SlackManager $slackManager, EntityManagerInterface $em, int $maxTransactionAmount)
    {
        $this->slackManager = $slackManager;
        $this->em = $em;
        $this->maxTransactionAmount = $maxTransactionAmount;
    }

    /**
     * Updates the list of users in database
     */
    public function updateUsersList() : array
    {
        $response = json_decode($this->slackManager->getSlackUsersList()->getBody()->getContents(), true);
        $rawUsers = $response['members'];
        $results = [
            'added' => [],
            'known' => []
        ];

        foreach ($rawUsers as $rawUser) {
            if ($rawUser['is_bot'] ||
                $rawUser['deleted'] ||
                $rawUser['is_ultra_restricted'] ||
                $rawUser['is_restricted'] ||
                $rawUser['name'] == 'slackbot'
            ) {
                continue;
            } else {
                $slackName =
                    $rawUser['profile']['display_name'] == ''
                        ? $rawUser['name']
                        : $rawUser['profile']['display_name'];

                $users[] = $rawUser['name'];
                $existingUser = $this->em->getRepository(User::class)
                    ->findUserBySlackId($rawUser['id']);

                if (null !== $existingUser) {
                    $user = $existingUser;
                    $results['known'][] = $slackName;
                } else {
                    $user = (new User())->setToGive(6);
                    $results['added'][] = $slackName;
                }

                $user
                    ->setSlackId($rawUser['id'])
                    ->setSlackName($slackName)
                    ->setSlackRealName($rawUser['profile']['real_name_normalized'])
                ;

                $this->em->persist($user);
            }
        }
        $this->em->flush();

        return $results;
    }

    public function registerMissingUser(string $userId)
    {
        $this->updateUsersList();
        $user = $this->em->getRepository(User::class)->findUserBySlackId($userId);
        if (null == $user) {
            throw new \Exception('Sorry, an error happened. I don\'t know you 🙊');
        }
        return $user;
    }

    /**
     * @param User $giver
     * @param User $receiver
     * @param int $amount
     * @param string $reason
     * @return Transaction
     * @throws \Exception
     */
    public function treatMriqs(User $giver, User $receiver, int $amount, string $reason)
    {
        //Check if user is trying to give briqs to himself
        if ($giver->getSlackId() == $receiver->getSlackId()) {
            throw new \Exception('Did you really think it was this easy ? Come on 😜 !');
        }

        //Check if transaction exceeds the limit
        if ($amount > $this->maxTransactionAmount) {
            $errorString = sprintf(
                'Go easy there, treats cannot exceed %s mriqs at the moment 💰',
                $this->maxTransactionAmount
            );
            throw new \Exception($errorString);
        }

        if ($amount == 0) {
            $errorString = sprintf(
                'Come on 😏, you have %smq to give, go spread some love ❤️ !',
                $giver->getToGive()
            );
            throw new \Exception($errorString);
        }

        //Check if user has enough briqs to give
        if ($giver->getToGive() >= $amount) {
            $gaveLastMriqs = $giver->getToGive() == $amount;

            $transaction = (new Transaction())
                ->setReason($reason)
                ->setAmount($amount)
                ->setGiver($giver)
                ->setReceiver($receiver)
                ->setWereLastMriqs($gaveLastMriqs)
            ;

            $receiver->receiveBriqs($amount);
            $giver->giveBriqs($amount);

            $this->em->persist($receiver);
            $this->em->persist($giver);
            $this->em->persist($transaction);

            return $transaction;
        } else {
            throw new \Exception(
                'Whooooops, you don\'t have enough mriqs to be this generous at the moment 💸, sorry 😢.'
            );
        }
    }

    /**
     * @param string $input
     * @return array
     */
    public function parseSlackTreatText(string $input)
    {
        preg_match('/^<@([^|]*)[^>]*>\s*(\d)\s?(.*)?/', $input, $matches, PREG_OFFSET_CAPTURE);

        if (count($matches) == 4) {
            $user = $this->em->getRepository(User::class)->findUserBySlackId($matches[1][0]);
            if (null == $user) {
                $user = $this->registerMissingUser($matches[1]);
            }

            return array(
                'user' => $user,
                'amount' => (int) $matches[2][0],
                'reason' => $matches[3][0]
            );
        } else {
            throw new \Exception('Whooops, I did\'nt quite get what you tried to say here 🙉');
        }
    }
}

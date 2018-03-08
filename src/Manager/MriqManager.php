<?php

namespace App\Manager;

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
     * MriqManager constructor.
     * @param SlackManager $slackManager
     */
    public function __construct(SlackManager $slackManager, EntityManagerInterface $em)
    {
        $this->slackManager = $slackManager;
        $this->em = $em;
    }

    /**
     * Updates the list of users in database
     */
    public function updateUsersList() : array
    {
        $response = json_decode($this->slackManager->getSlackUsersList()->getBody()->getContents(), true);
        $rawUsers = $response['members'];
        $results = [];

        foreach ($rawUsers as $rawUser) {
            if ($rawUser['is_bot'] ||
                $rawUser['deleted'] ||
                $rawUser['is_ultra_restricted'] ||
                $rawUser['is_restricted'] ||
                $rawUser['name'] == 'slackbot'
            ) {
                continue;
            } else {
                $users[] = $rawUser['name'];
                $existingUser = $this->em->getRepository(User::class)
                    ->findUserBySlackId($rawUser['id']);

                if (null !== $existingUser) {
                    $user = $existingUser;
                    $results['added'][] = $rawUser['name'];
                } else {
                    $user = new User();
                    $results['known'][] = $rawUser['name'];
                }

                $user
                    ->setSlackId($rawUser['id'])
                    ->setSlackName($rawUser['name'])
                    ->setSlackRealName($rawUser['profile']['real_name_normalized']);

                $this->em->persist($user);
            }
        }
        $this->em->flush();

        return $results;
    }

    public function treatMriqs(User $giver, User $receiver, int $amount, string $reason)
    {

    }

}
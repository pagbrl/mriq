<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Manager\MriqManager;
use App\Manager\SlackManager;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{

    /**
     * @Route("/")
     * @return JsonResponse
     */
    public function indexAction()
    {
        return new JsonResponse('Greetings stranger, I am Mriq, it is nice meeting you !');
    }

    /**
     * @Route("/treat", name="treat")
     */
    public function treatAction(
        EntityManagerInterface $em,
        MriqManager $mriqManager,
        SlackManager $slackManager,
        Request $request
    ) {
        $slackPayload = $request->request->all();

        try {
            $giver = $em->getRepository(User::class)->findUserBySlackId($slackPayload['user_id']);

            if (null == $giver) {
                $giver = $mriqManager->registerMissingUser($slackPayload['user_id']);
            }

            $mriqChannelId = $this->getParameter('mriq_channel_id');

            $parsedData = $mriqManager->parseSlackTreatText($slackPayload['text']);
            /** @var User $receiver */
            $receiver = $parsedData['user'];
            /** @var int $amount */
            $amount = $parsedData['amount'];
            /** @var string $reason */
            $reason = $parsedData['reason'];

            /** @var Transaction $transaction */
            $transaction = $mriqManager->treatMriqs($giver, $receiver, $amount, $reason);

            $confirmGiverString = $transaction->getWereLastMriqs() ?
                sprintf(
                    "You gave your last *%s* mriqs to *@%s* ! Thanks for spreading the love 💌",
                    $amount,
                    $receiver->getSlackName()
                )
                :
                sprintf(
                    "You gave *%s* mriqs to *@%s* ! Thanks for spreading the love 💌",
                    $amount,
                    $receiver->getSlackName()
                );

            $confirmReceiverString = $transaction->getWereLastMriqs() ?
                sprintf(
                    "*@%s* just gave you their last *%smriqs* (You now have %smq)",
                    $giver->getSlackName(),
                    $amount,
                    $receiver->getTotalEarned()
                )
                :
                sprintf(
                    "You just received *%smq* from *@%s* (You know have %smq)",
                    $amount,
                    $giver->getSlackName(),
                    $receiver->getTotalEarned()
                );

            $receiverActionAttachment = array(
                0 => array(
                    'text' => $reason,
                    'callback_id' => 'reaction',
                    'actions' => array(
                        0 => array(
                            'name' => 'reaction',
                            'text' => '❤️',
                            'type' => 'button',
                            'value' => Transaction::REACTION_HEART
                        ),
                        1 => array(
                            'name' => 'reaction',
                            'text' => '😂',
                            'type' => 'button',
                            'value' => Transaction::REACTION_JOY
                        ),
                        2 => array(
                            'name' => 'reaction',
                            'text' => '👍',
                            'type' => 'button',
                            'value' => Transaction::REACTION_THUMBSUP
                        )
                    )

                )
            );

            $logToMriqChannelString = sprintf(
                "*@%s* gave %smq to *@%s*",
                $giver->getSlackName(),
                $amount,
                $receiver->getSlackName(),
                $reason
            );

            $reasonAttachment = array(
                0 => array('text' => $reason)
            );

            //Sending confirmation for the giver
            $slackManager->sendEphemeralMessage($slackPayload['channel_id'], $confirmGiverString, $giver->getSlackId());

            //Sending good news to the receiver
            $slackManager->sendMessage($receiver->getSlackId(), $confirmReceiverString, $receiverActionAttachment);

            //Logging the activity to the mriq channel
            $mriqMessage = json_decode(
                $slackManager->sendMessage(
                    $mriqChannelId,
                    $logToMriqChannelString,
                    $reasonAttachment
                ),
                false
            );

            $transaction->setMriqChannelMessageTs($mriqMessage['ts']);
            $em->persist($transaction);
            $em->flush();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() == "" ? 'Whoops, something went wrong 🙈' : sprintf(
                '%s - %s - l.%s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );
            $slackManager->sendEphemeralMessage(
                $slackPayload['channel_id'],
                $errorMessage,
                $slackPayload['user_id']
            );
        }

        return new Response();
    }

    /**
     * @Route("/mriq", name="mriq")
     */
    public function mriqAction(
        EntityManagerInterface $em,
        MriqManager $mriqManager,
        SlackManager $slackManager,
        Request $request
    ) {
        $slackPayload = $request->request->all();

        try {
            $user = $em->getRepository(User::class)->findUserBySlackId($slackPayload['user_id']);

            if (null == $user) {
                $user = $mriqManager->registerMissingUser($slackPayload['user_id']);
            }

            $string = sprintf(
                "Hey *@%s*, you currently have *%smq* left to give and received *%smq* total ! \n Time to spread some love 💖",
                $user->getSlackName(),
                $user->getToGive(),
                $user->getTotalEarned()
            );

            $slackManager->sendEphemeralMessage($slackPayload['channel_id'], $string, $user->getSlackId());
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() == "" ? 'Whoops, something went wrong 🙈' : $e->getMessage();
            $slackManager->sendEphemeralMessage(
                $slackPayload['channel_id'],
                $errorMessage,
                $slackPayload['user_id']
            );
        }

        return new Response();
    }

    /**
     * @Route("/reaction", name="mriq")
     */
    public function reactionAction(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        MriqManager $mriqManager,
        SlackManager $slackManager,
        Request $request
    ) {

        $slackPayload = json_decode($request->request->get('payload'), true);

        $logger->debug(json_encode($slackPayload));

        try {
            //Update transaction object
            /** @var Transaction $transaction */
            $transaction = $em->getRepository(Transaction::class)
                ->findOneByMriqSlackbotMessageTs($slackPayload['original_message']['ts']);

            if (null == $transaction) {
                throw new \Exception('Whoops, I could not find the transaction you are trying to react to 🤔');
            }

            $reaction = $slackPayload['actions'][0]['value'];

            if (in_array($reaction, Transaction::AVAILABLE_REACTIONS)) {
                $transaction->setReaction($reaction);
            } else {
                throw new \Exception('Wow, this is an unexpected reaction !');
            }

            $receiverSlackbotAttachments = array(
                0 => array('text' => $transaction->getReason()),
                1 => array('text' => sprintf(
                    'You reacted with :%s:',
                    $transaction->getReaction()
                ))
            );

            $receiverSlackbotString = sprintf(
                '*@%s* gave you *%smq* (You now have %smq)',
                $transaction->getGiver()->getSlackName(),
                $transaction->getAmount(),
                $transaction->getReceiver()->getTotalEarned()
            );

            $giverSlackbotAttachments = array(
                0 => array('text' => sprintf(
                    '*@%s* gave %smq to *@%s*',
                    $transaction->getGiver()->getSlackName(),
                    $transaction->getAmount(),
                    $transaction->getReceiver()->getSlackName()
                )),
                1 => array('text' => $transaction->getReason())
            );

            $giverSlackbotString = sprintf(
                '*@%s* reacted with :%s:',
                $transaction->getReceiver()->getSlackName(),
                $transaction->getReaction()
            );

//            $logMessageString = sprintf('');
//
//            $logMessageAttachments = array();

            //Respond to message directly to update message in receiver's slackbot
            $slackManager->respondToAction(
                $slackPayload['response_url'],
                $receiverSlackbotString,
                $receiverSlackbotAttachments
            );

            //Notify giver of reaction
            $slackManager->sendMessage(
                $transaction->getGiver()->getSlackId(),
                $giverSlackbotString,
                $giverSlackbotAttachments
            );

//            //Update log message in #mriq
//            $slackManager->updateChat(
//                $transaction->getMriqChannelMessageTs(),
//                $logMessageString,
//                $logMessageAttachments
//            )

            $em->persist($transaction);
            $em->flush();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() == "" ? 'Whoops, something went wrong 🙈' : sprintf(
                '%s - %s - l.%s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );
            $slackManager->sendEphemeralMessage(
                $slackPayload['channel']['id'],
                $errorMessage,
                $slackPayload['user_id']
            );
        }

        return new Response();
    }
}

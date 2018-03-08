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
                    "You just received *%s* mriqs from *@%s* : _%s_ ",
                    $amount,
                    $giver->getSlackName(),
                    $reason
                )
                :
                sprintf(
                    "*%s* just gave you their last *%s* mriqs : _%s_",
                    $giver->getSlackName(),
                    $amount,
                    $reason
                );

            $logToMriqChannelString = sprintf(
                "*@s%* gave %smq to *@%s% \n > %s",
                $giver->getSlackName(),
                $amount,
                $receiver->getSlackName(),
                $reason
            );

            //Sending confirmation for the giver
            $slackManager->sendEphemeralMessage($slackPayload['channel_id'], $confirmGiverString, $giver->getSlackId());

            //Sending good news to the receiver
            $slackManager->sendMessage($receiver->getSlackId(), $confirmReceiverString);

            //Logging the activity to the mriq channel
//            $slackManager->sendMessage($mriqChannelId, $logToMriqChannelString);
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
}

<?php

namespace App\Controller;

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
        LoggerInterface $logger,
        EntityManagerInterface $em,
        MriqManager $mriqManager,
        SlackManager $slackManager,
        Request $request
    ) {
        $slackPayload = $request->request->all();

        $logger->debug(json_encode($slackPayload));

        try {
            $giver = $em->getRepository(User::class)->findUserBySlackId($slackPayload['user_id']);

            if (null == $giver) {
                $giver = $mriqManager->registerMissingUser($slackPayload['user_id']);
            }

            $parsedData = $mriqManager->parseSlackTreatText($slackPayload['text']);

            $string = sprintf(
                '%s gave %s %s mriqs because : %s',
                $giver->getSlackName(),
                $parsedData['amount'],
                $parsedData['user']->getSlackName(),
                $parsedData['reason']
            );
            $slackManager->sendEphemeralMessage($slackPayload['channel_id'], $string, $giver->getSlackId());


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
    public function mriqAction(Request $request)
    {

        //Send empty response (Botman has already sent the output itself - https://github.com/botman/botman/issues/342)
        return new Response();
    }
}

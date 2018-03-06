<?php

namespace App\Controller;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Middleware\ApiAi;
use BotMan\Drivers\Slack\SlackDriver;
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
        return new JsonResponse('Coucou petite perruche');
    }

    /**
     * @Route("/botman", name="message")
     */
    public function messageAction(Request $request)
    {
        // Create a BotMan instance, using the WebDriver
        DriverManager::loadDriver(SlackDriver::class);

        $config = [
            'slack' => [
                'token' => $this->getParameter('slack_token')
            ]
        ];

        $botman = BotManFactory::create($config); //No config options required

        //Send empty response (Botman has already sent the output itself - https://github.com/botman/botman/issues/342)
        return new Response();
    }

    /**
     * @Route("/treat", name="treat")
     */
    public function treatAction(LoggerInterface $logger, Request $request)
    {
        $slackToken = $this->getParameter('slack_token');
        $logger->debug($slackToken);
        $config = [
            'slack' => [
                'token' => $slackToken
            ]
        ];

        // Create a BotMan instance, using the WebDriver
        DriverManager::loadDriver(SlackDriver::class);
        $botman = BotManFactory::create($config, null, $request);

        $logger->debug($botman->getUser()->getUsername());

        $botman->hears('hi', function (BotMan $bot) use ($logger) {
            $logger->debug('hi coucou');
            $bot->reply(sprintf(
                'I heard you %s ! :)',
                $bot->getUser()->getUsername()
            ));
        });

        //Send empty response (Botman has already sent the output itself - https://github.com/botman/botman/issues/342)
        return new Response();
    }

    /**
     * @Route("/mbriqs", name="mbriqs")
     */
    public function mbriqsAction(Request $request)
    {
        // Create a BotMan instance, using the WebDriver
        DriverManager::loadDriver(SlackDriver::class);

        $config = [
            'slack' => [
                'token' => $this->getParameter('slack_token')
            ]
        ];

        $botman = BotManFactory::create($config); //No config options required

        //Send empty response (Botman has already sent the output itself - https://github.com/botman/botman/issues/342)
        return new Response();
    }

    /**
     * @Route("/interactive", name="interactive")
     */
    public function interactiveAction(Request $request)
    {
        // Create a BotMan instance, using the WebDriver
        DriverManager::loadDriver(SlackDriver::class);

        $config = [
            'slack' => [
                'token' => ''
            ]
        ];

        $botman = BotManFactory::create($config); //No config options required

        //Send empty response (Botman has already sent the output itself - https://github.com/botman/botman/issues/342)
        return new Response();
    }
}

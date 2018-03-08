<?php

namespace App\Controller;

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
    public function messageAction(LoggerInterface $logger, Request $request)
    {
        $logger->debug(json_encode($request->request->all()));

        return new Response();
    }

    /**
     * @Route("/mriq", name="mriq")
     */
    public function treatAction(Request $request)
    {

        //Send empty response (Botman has already sent the output itself - https://github.com/botman/botman/issues/342)
        return new Response();
    }
}

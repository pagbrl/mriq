<?php

namespace App\Controller;

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

        return new Response();
    }

    /**
     * @Route("/treat", name="treat")
     */
    public function treatAction(Request $request)
    {

        //Send empty response (Botman has already sent the output itself - https://github.com/botman/botman/issues/342)
        return new Response();
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    public function index(Request $request): Response
    {
        return new JsonResponse([
            'status' => 'ok',
            'service' => 'Paxful-wallets-api',
        ]);
    }
}
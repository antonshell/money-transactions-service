<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends AbstractController implements AuthenticatedController
{
    public function create(Request $request): Response
    {
        return new JsonResponse([
            'status' => 'ok',
            'service' => 'create',
        ]);
    }
}
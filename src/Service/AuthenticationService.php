<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationService
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    public function getUserFromRequest(): ?User
    {
        $username = $this->createRequest()->server->get('HTTP_USERNAME');

        return $this->userRepository->findByEmail($username);
    }

    public function getPasswordFromRequest(): ?string
    {
        return $this->createRequest()->server->get('HTTP_PASSWORD');
    }

    private function createRequest(): Request
    {
        return Request::createFromGlobals();
    }
}
<?php

namespace App\Service;

use App\Repository\UserRepository;

class DashboardService
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

    public function getAllUsersWallets(): array
    {
        $users = $this->userRepository->findAll();

        $data = [];
        foreach ($users as $user) {
            $wallets = [];
            foreach ($user->getWallets() as $wallet) {
                $wallets[] = [
                    'id' => $wallet->getId(),
                    'name' => $wallet->getName(),
                    'currency' => $wallet->getCurrency(),
                    'balance' => $wallet->getBalance(),
                ];
            }

            $data[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'wallets' => $wallets
            ];
        }

        return $data;
    }
}
<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\CurrencyEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadSampleData extends Command
{
    protected static $defaultName = 'sample-data:load';

    private $config = [
        [
            'name' => 'User 1',
            'email' => 'user1@test.com',
            'password' => 'user1',
            'wallets' => [
                [
                    'name' => 'BTC #1',
                    'balance' => 10,
                    'currency' => CurrencyEnum::BTC,
                ],
                [
                    'name' => 'ETH #1',
                    'balance' => 5,
                    'currency' => CurrencyEnum::ETH,
                ]
            ]
        ],
        [
            'name' => 'User 2',
            'email' => 'user2@test.com',
            'password' => 'user2',
            'wallets' => [
                [
                    'name' => 'BTC #2',
                    'balance' => 10,
                    'currency' => CurrencyEnum::BTC,
                ],
                [
                    'name' => 'ETH #2',
                    'balance' => 5,
                    'currency' => CurrencyEnum::ETH,
                ]
            ]
        ]
    ];

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Load sample data - users and wallets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager->getConnection()->executeQuery('DELETE FROM transaction');
        $this->entityManager->getConnection()->executeQuery('DELETE FROM wallet');
        $this->entityManager->getConnection()->executeQuery('DELETE FROM user');

        foreach ($this->config as $userRow){
            $user = new User();
            $user
                ->setName($userRow['name'])
                ->setEmail($userRow['email'])
                ->setPassword(password_hash($userRow['password'], PASSWORD_DEFAULT));
            $this->entityManager->persist($user);

            foreach ($userRow['wallets'] as $walletRow){
                $wallet1 = new Wallet();
                $wallet1
                    ->setUser($user)
                    ->setName($walletRow['name'])
                    ->setBalance($walletRow['balance'])
                    ->setCurrency($walletRow['currency']);
                $this->entityManager->persist($wallet1);
            }
        }

        $this->entityManager->flush();
        $output->writeln('Job done!');

        return Command::SUCCESS;
    }
}
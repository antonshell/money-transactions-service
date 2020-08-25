<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadSampleData extends Command
{
    protected static $defaultName = 'sample-data:load';

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
        $output->writeln('Remove old data...');

        $this->entityManager->getConnection()->executeQuery('DELETE FROM transaction');
        $this->entityManager->getConnection()->executeQuery('DELETE FROM wallet');
        $this->entityManager->getConnection()->executeQuery('DELETE FROM user');

        $output->writeln('Create users...');

        $user1 = new User();
        $user1->setName('User 1');
        $user1->setEmail('user1@test.com');
        $user1->setPassword(password_hash('user1', PASSWORD_DEFAULT));
        $this->entityManager->persist($user1);

        $user2 = new User();
        $user2->setName('User 2');
        $user2->setEmail('user2@test.com');
        $user2->setPassword(password_hash('user2', PASSWORD_DEFAULT));
        $this->entityManager->persist($user2);

        $user3 = new User();
        $user3->setName('User 3');
        $user3->setEmail('user3@test.com');
        $user3->setPassword(password_hash('user3', PASSWORD_DEFAULT));
        $this->entityManager->persist($user3);

        $this->entityManager->flush();

        $output->writeln('Job done!');

        return Command::SUCCESS;
    }
}
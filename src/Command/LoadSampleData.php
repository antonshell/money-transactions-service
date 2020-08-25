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

        $product = new User();
        $product->setName('User 1');
        $product->setEmail('user1@test.com');
        $product->setPassword('user1');
        $this->entityManager->persist($product);

        $product = new User();
        $product->setName('User 2');
        $product->setEmail('user2@test.com');
        $product->setPassword('user2');
        $this->entityManager->persist($product);

        $product = new User();
        $product->setName('User 3');
        $product->setEmail('user3@test.com');
        $product->setPassword('user3');
        $this->entityManager->persist($product);

        $this->entityManager->flush();

        $output->writeln('Job done!');

        return Command::SUCCESS;
    }
}
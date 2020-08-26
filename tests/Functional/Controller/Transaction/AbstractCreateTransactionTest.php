<?php

namespace App\Tests\Functional\Controller\Transaction;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use App\Tests\Functional\FixtureWebTestCase;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractCreateTransactionTest extends FixtureWebTestCase
{
    /**
     * @var TransactionRepository
     */
    protected $transactionRepository;

    public function setUp(): void
    {
        parent::setUp();
        $container = self::$kernel->getContainer();
        $this->transactionRepository = $container->get('doctrine')->getRepository(Transaction::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->transactionRepository);
    }

    protected function authorize(string $email, string $password): void
    {
        $_SERVER['HTTP_USERNAME'] = $email;
        $_SERVER['HTTP_PASSWORD'] = $password;
    }

    protected function createTransaction(array $data)
    {
        $this->client->request(
            Request::METHOD_POST,
            '/transaction',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
    }
}

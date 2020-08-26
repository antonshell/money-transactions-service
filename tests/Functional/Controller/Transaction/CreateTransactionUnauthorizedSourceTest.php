<?php

namespace App\Tests\Functional\Controller\Transaction;

use App\Entity\Wallet;
use App\Enum\CurrencyEnum;
use App\Tests\DataFixtures\Controller\DefaultFixture;
use Symfony\Component\HttpFoundation\Response;

class CreateTransactionUnauthorizedSourceTest extends AbstractCreateTransactionTest
{
    public function assertPreConditions()
    {
        parent::assertPreConditions();

        /** @var Wallet $source */
        $source = $this->getReference(DefaultFixture::REF_WALLET_BTC1);

        /** @var Wallet $destination */
        $destination = $this->getReference(DefaultFixture::REF_WALLET_BTC2);

        // check preconditions
        $this->assertEmpty($this->transactionRepository->findAll());

        $this->assertEquals(DefaultFixture::EMAIL1, $source->getUser()->getEmail());
        $this->assertEquals(10, $source->getBalance());
        $this->assertEquals(CurrencyEnum::BTC, $source->getCurrency());

        $this->assertEquals(DefaultFixture::EMAIL2, $destination->getUser()->getEmail());
        $this->assertEquals(7, $destination->getBalance());
        $this->assertEquals(CurrencyEnum::BTC, $destination->getCurrency());
    }

    public function testCreateTransactionUnauthorizedSource(): void
    {
        $this->authorize(DefaultFixture::EMAIL2, DefaultFixture::PASSWORD1);

        /** @var Wallet $source */
        $source = $this->getReference(DefaultFixture::REF_WALLET_BTC1);

        /** @var Wallet $destination */
        $destination = $this->getReference(DefaultFixture::REF_WALLET_BTC2);

        // create transaction
        $this->createTransaction([
            'source' => 123,
            'destination'=> $destination->getId(),
            'amount'=> 2
        ]);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
        $this->checkContentError([
            'field' => 'exception',
            'message' => 'Invalid username or password'
        ]);

        // check transaction
        $this->assertEmpty($this->transactionRepository->findAll());

        // check wallets
        $this->assertEquals(10, $source->getBalance());
        $this->assertEquals(7, $destination->getBalance());
    }

    /**
     * {@inheritdoc}
     */
    protected function getFixtures(): array
    {
        return [
            DefaultFixture::class,
        ];
    }
}

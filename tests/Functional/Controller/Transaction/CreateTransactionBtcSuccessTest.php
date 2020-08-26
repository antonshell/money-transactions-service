<?php

namespace App\Tests\Functional\Controller\Transaction;

use App\Entity\Wallet;
use App\Enum\CommissionEnum;
use App\Enum\CurrencyEnum;
use App\Tests\DataFixtures\Controller\DefaultFixture;
use Symfony\Component\HttpFoundation\Response;

class CreateTransactionBtcSuccessTest extends AbstractCreateTransactionTest
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

    public function testCreateTransactionSuccess(): void
    {
        $this->authorize(DefaultFixture::EMAIL1, DefaultFixture::PASSWORD1);

        /** @var Wallet $source */
        $source = $this->getReference(DefaultFixture::REF_WALLET_BTC1);

        /** @var Wallet $destination */
        $destination = $this->getReference(DefaultFixture::REF_WALLET_BTC2);

        // create transaction
        $this->createTransaction([
            'source' => $source->getId(),
            'destination'=> $destination->getId(),
            'amount'=> 2
        ]);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // check transaction
        $transactions = $this->transactionRepository->findAll();
        $this->assertCount(1, $transactions);

        $transaction = $transactions[0];
        $this->assertEquals($source, $transaction->getSource());
        $this->assertEquals($destination, $transaction->getDestination());
        $this->assertEquals(CurrencyEnum::BTC, $transaction->getCurrency());
        $this->assertEquals(2, $transaction->getAmount());
        $this->assertEquals(CommissionEnum::DEFAULT, $transaction->getCommissionPercent());
        $this->assertEquals(0.03, $transaction->getCommissionAmount());

        // check wallets
        $this->assertEquals(7.97, $source->getBalance());
        $this->assertEquals(9, $destination->getBalance());
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

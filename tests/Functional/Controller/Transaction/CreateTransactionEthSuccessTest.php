<?php

namespace App\Tests\Functional\Controller\Transaction;

use App\Entity\Wallet;
use App\Enum\CommissionEnum;
use App\Enum\CurrencyEnum;
use App\Tests\DataFixtures\Controller\DefaultFixture;
use Symfony\Component\HttpFoundation\Response;

class CreateTransactionEthSuccessTest extends AbstractCreateTransactionTest
{
    public function assertPreConditions()
    {
        parent::assertPreConditions();

        /** @var Wallet $source */
        $source = $this->getReference(DefaultFixture::REF_WALLET_ETH1);

        /** @var Wallet $destination */
        $destination = $this->getReference(DefaultFixture::REF_WALLET_ETH2);

        // check preconditions
        $this->assertEmpty($this->transactionRepository->findAll());

        $this->assertEquals(DefaultFixture::EMAIL1, $source->getUser()->getEmail());
        $this->assertEquals(5, $source->getBalance());
        $this->assertEquals(CurrencyEnum::ETH, $source->getCurrency());

        $this->assertEquals(DefaultFixture::EMAIL2, $destination->getUser()->getEmail());
        $this->assertEquals(4, $destination->getBalance());
        $this->assertEquals(CurrencyEnum::ETH, $destination->getCurrency());
    }

    public function testCreateTransactionSuccess(): void
    {
        $this->authorize(DefaultFixture::EMAIL1, DefaultFixture::PASSWORD1);

        /** @var Wallet $source */
        $source = $this->getReference(DefaultFixture::REF_WALLET_ETH1);

        /** @var Wallet $destination */
        $destination = $this->getReference(DefaultFixture::REF_WALLET_ETH2);

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
        $this->assertEquals(CurrencyEnum::ETH, $transaction->getCurrency());
        $this->assertEquals(2, $transaction->getAmount());
        $this->assertEquals(CommissionEnum::DEFAULT, $transaction->getCommissionPercent());
        $this->assertEquals(0.03, $transaction->getCommissionAmount());

        // check wallets
        $this->assertEquals(2.97, $source->getBalance());
        $this->assertEquals(6, $destination->getBalance());
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

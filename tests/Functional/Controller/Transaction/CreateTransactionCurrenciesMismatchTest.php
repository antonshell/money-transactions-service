<?php

namespace App\Tests\Functional\Controller\Transaction;

use App\Entity\Wallet;
use App\Enum\CommissionEnum;
use App\Enum\CurrencyEnum;
use App\Tests\DataFixtures\Controller\DefaultFixture;
use Symfony\Component\HttpFoundation\Response;

class CreateTransactionCurrenciesMismatchTest extends AbstractCreateTransactionTest
{
    public function assertPreConditions()
    {
        parent::assertPreConditions();

        /** @var Wallet $source */
        $source = $this->getReference(DefaultFixture::REF_WALLET_BTC1);

        /** @var Wallet $destination */
        $destination = $this->getReference(DefaultFixture::REF_WALLET_ETH2);

        // check preconditions
        $this->assertEmpty($this->transactionRepository->findAll());

        $this->assertEquals(DefaultFixture::EMAIL1, $source->getUser()->getEmail());
        $this->assertEquals(10, $source->getBalance());
        $this->assertEquals(CurrencyEnum::BTC, $source->getCurrency());

        $this->assertEquals(DefaultFixture::EMAIL2, $destination->getUser()->getEmail());
        $this->assertEquals(4, $destination->getBalance());
        $this->assertEquals(CurrencyEnum::ETH, $destination->getCurrency());
    }

    public function testCreateTransactionNotEnoughFunds(): void
    {
        $this->authorize(DefaultFixture::EMAIL1, DefaultFixture::PASSWORD1);

        /** @var Wallet $source */
        $source = $this->getReference(DefaultFixture::REF_WALLET_BTC1);

        /** @var Wallet $destination */
        $destination = $this->getReference(DefaultFixture::REF_WALLET_ETH2);

        // create transaction
        $this->createTransaction([
            'source' => $source->getId(),
            'destination'=> $destination->getId(),
            'amount'=> 1
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        $this->checkContentError([
            'field' => 'destination',
            'message' => 'Currencies mismatch'
        ]);

        // check transaction
        $this->assertEmpty($this->transactionRepository->findAll());

        // check wallets
        $this->assertEquals(10, $source->getBalance());
        $this->assertEquals(4, $destination->getBalance());
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

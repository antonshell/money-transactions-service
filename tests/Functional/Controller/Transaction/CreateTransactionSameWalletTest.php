<?php

namespace App\Tests\Functional\Controller\Transaction;

use App\Entity\Wallet;
use App\Enum\CurrencyEnum;
use App\Tests\DataFixtures\Controller\DefaultFixture;
use Symfony\Component\HttpFoundation\Response;

class CreateTransactionSameWalletTest extends AbstractCreateTransactionTest
{
    public function assertPreConditions()
    {
        parent::assertPreConditions();

        /** @var Wallet $source */
        $source = $this->getReference(DefaultFixture::REF_WALLET_ETH1);

        /** @var Wallet $destination */
        $destination = $this->getReference(DefaultFixture::REF_WALLET_ETH1);

        // check preconditions
        $this->assertEmpty($this->transactionRepository->findAll());

        $this->assertEquals(DefaultFixture::EMAIL1, $source->getUser()->getEmail());
        $this->assertEquals(5, $source->getBalance());
        $this->assertEquals(CurrencyEnum::ETH, $source->getCurrency());

        $this->assertEquals($destination, $source);
    }

    public function testCreateTransactionSameWalletTest(): void
    {
        $this->authorize(DefaultFixture::EMAIL1, DefaultFixture::PASSWORD1);

        /** @var Wallet $source */
        $source = $this->getReference(DefaultFixture::REF_WALLET_ETH1);

        /** @var Wallet $destination */
        $destination = $this->getReference(DefaultFixture::REF_WALLET_ETH1);

        // create transaction
        $this->createTransaction([
            'source' => $source->getId(),
            'destination'=> $destination->getId(),
            'amount'=> 2
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->checkContentError([
            'field' => 'destination',
            'message' => 'Transfer to same wallet is forbidden'
        ]);

        // check transaction
        $this->assertEmpty($this->transactionRepository->findAll());

        // check wallets
        $this->assertEquals(5, $source->getBalance());
        $this->assertEquals($source, $destination);
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

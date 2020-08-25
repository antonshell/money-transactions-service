<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Enum\CommissionEnum;
use App\Http\Request\TransactionRequest;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;

class TransactionCreator
{
    private const FIELD_SOURCE = 'source';
    private const FIELD_DESTINATION = 'destination';

    private const ERROR_INVALID_SOURCE = 'Invalid source wallet';
    private const ERROR_INVALID_DESTINATION = 'Invalid destination wallet';
    private const ERROR_CURRENCIES_MISMATCH = 'Currencies mismatch';
    private const ERROR_NOT_ENOUGH_FUNDS = 'Not enough funds';

    /**
     * @var WalletRepository
     */
    private $walletRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var string[]
     */
    private $errors = [];

    public function __construct(
        WalletRepository $walletRepository,
        EntityManagerInterface $entityManager,
        AuthenticationService $authenticationService
    )
    {
        $this->walletRepository = $walletRepository;
        $this->entityManager = $entityManager;
        $this->authenticationService = $authenticationService;
    }

    public function create(TransactionRequest $transactionRequest): ?Transaction
    {
        $source = $this->walletRepository->find($transactionRequest->getSource());
        $destination = $this->walletRepository->find($transactionRequest->getDestination());

        $user = $this->authenticationService->getUserFromRequest();
        if ($source === null || $source->getUser() !== $user) {
            $this->errors[self::FIELD_SOURCE][] = self::ERROR_INVALID_SOURCE;
            return null;
        }

        if ($destination === null) {
            $this->errors[self::FIELD_DESTINATION][] = self::ERROR_INVALID_DESTINATION;
            return null;
        }

        if ($destination->getCurrency() !== $source->getCurrency()) {
            $this->errors[self::FIELD_DESTINATION][] = self::ERROR_CURRENCIES_MISMATCH;
            return null;
        }

        // @todo check user
        $transaction = new Transaction();
        $transaction->setSource($source);
        $transaction->setDestination($destination);
        $transaction->setAmount($transactionRequest->getAmount());
        $transaction->setCommissionPercent(CommissionEnum::DEFAULT);
        $transaction->setCommissionAmount($transactionRequest->getAmount() * CommissionEnum::DEFAULT);
        $transaction->setCurrency($source->getCurrency());
        $transaction->setCreatedAt(new \DateTime());

        $sourceBalance = $source->getBalance() - $transaction->getAmount() - $transaction->getCommissionAmount();
        $source->setBalance($sourceBalance);

        $destinationBalance = $destination->getBalance() + $transaction->getAmount();
        $destination->setBalance($destinationBalance);

        if ($sourceBalance < 0) {
            $this->errors[self::FIELD_SOURCE][] = self::ERROR_NOT_ENOUGH_FUNDS;
            return null;
        }

        $this->entityManager->persist($transaction);
        $this->entityManager->persist($source);
        $this->entityManager->persist($destination);

        $this->entityManager->flush();

        return $transaction;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Enum\CommissionEnum;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TransactionController extends AbstractController implements AuthenticatedController
{
    /**
     * @var WalletRepository
     */
    private $walletRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        WalletRepository $walletRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->walletRepository = $walletRepository;
        $this->entityManager = $entityManager;
    }

    public function create(Request $request): Response
    {
        // @todo use fos rest bundle
        $data = $this->getRequestParams($request);

        $sourceId = $data['source'];
        $destinationId = $data['destination'];
        $amount = $data['amount'];

        $source = $this->walletRepository->find($sourceId);
        $destination = $this->walletRepository->find($destinationId);

        // @todo check user
        // @todo add errors handling
        // @todo check currencies match

        $transaction = new Transaction();
        $transaction->setSource($source);
        $transaction->setDestination($destination);
        $transaction->setAmount($amount);
        $transaction->setCommissionPercent(CommissionEnum::DEFAULT);
        $transaction->setCommissionAmount($amount * CommissionEnum::DEFAULT);
        $transaction->setCurrency($source->getCurrency());
        $transaction->setCreatedAt(new \DateTime());

        $this->entityManager->persist($transaction);

        $source->setBalance($source->getBalance() - $transaction->getAmount());
        $this->entityManager->persist($source);

        $destination->setBalance($destination->getBalance() + $transaction->getAmount() - $transaction->getCommissionAmount());
        $this->entityManager->persist($destination);

        $this->entityManager->flush();
        
        return new JsonResponse([
            'status' => 'ok',
            'transaction_id' => $transaction->getId(),
        ]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getRequestParams(Request $request)
    {
        if ($request->headers->get('Content-Type') !== 'application/json') {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid content type header. Must be application/json');
        }

        $params = json_decode($request->getContent(), true);

        return $params;
    }
}
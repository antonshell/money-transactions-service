<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Enum\CommissionEnum;
use App\Http\Request\TransactionRequest;
use App\Http\Response\ValidationErrorResponse;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransactionController extends AbstractController implements AuthenticatedController
{
    use ConvertViolationsTrait;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var WalletRepository
     */
    private $walletRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        ValidatorInterface $validator,
        WalletRepository $walletRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->validator = $validator;
        $this->walletRepository = $walletRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @ParamConverter(
     *      "transactionRequest",
     *      converter="fos_rest.request_body",
     *      class="App\Http\Request\TransactionRequest"
     * )
     *
     * @param TransactionRequest $transactionRequest
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function create(TransactionRequest $transactionRequest): Response
    {
        $violations = $this->validator->validate($transactionRequest);
        if ($violations->count() > 0) {
            return new ValidationErrorResponse(
                $this->convertViolations($violations),
                Response::HTTP_BAD_REQUEST
            );
        }

        $source = $this->walletRepository->find($transactionRequest->getSource());
        $destination = $this->walletRepository->find($transactionRequest->getDestination());

        // @todo check user
        // @todo add errors handling
        // @todo check currencies match

        $transaction = new Transaction();
        $transaction->setSource($source);
        $transaction->setDestination($destination);
        $transaction->setAmount($transactionRequest->getAmount());
        $transaction->setCommissionPercent(CommissionEnum::DEFAULT);
        $transaction->setCommissionAmount($transactionRequest->getAmount() * CommissionEnum::DEFAULT);
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
<?php

namespace App\Controller;

use App\Http\Request\TransactionRequest;
use App\Http\Response\ValidationErrorResponse;
use App\Service\TransactionCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @var TransactionCreator
     */
    private $transactionCreator;

    public function __construct(
        ValidatorInterface $validator,
        TransactionCreator $transactionCreator
    )
    {
        $this->validator = $validator;
        $this->transactionCreator = $transactionCreator;
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

        $transaction = $this->transactionCreator->create($transactionRequest);

        if(!$transaction){
            return new ValidationErrorResponse(
                $this->transactionCreator->getErrors(),
                Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse([
            'status' => 'ok',
            'transaction_id' => $transaction->getId(),
        ]);
    }
}
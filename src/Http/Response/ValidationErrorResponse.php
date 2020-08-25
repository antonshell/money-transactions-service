<?php

declare(strict_types=1);

namespace App\Http\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ValidationErrorResponse extends JsonResponse
{
    private const ERROR_INVALID_PARAMETERS = 'Invalid parameters';

    public function __construct(array $errors, int $status = self::HTTP_BAD_REQUEST, array $headers = [], bool $json = false)
    {
        $data = $this->convertErrors($errors);

        parent::__construct($data, $status, $headers, $json);
    }

    private function convertErrors(array $errors): array
    {
        $data = [];
        foreach ($errors as $field => $error) {
            $data[] = [
                'field' => $field,
                'message' => $error,
            ];
        }

        return [
            'status' => 'error',
            'message' => self::ERROR_INVALID_PARAMETERS,
            'data' => $data,
        ];
    }
}

<?php

namespace App\Controller;

use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ConvertViolationsTrait
{
    /**
     * @param ConstraintViolationListInterface $violations
     * @return array
     */
    protected function convertViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $errors;
    }
}
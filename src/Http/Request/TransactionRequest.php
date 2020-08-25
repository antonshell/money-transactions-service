<?php

namespace App\Http\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class TransactionRequest
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Assert\Type("integer")
     * @Assert\Positive()
     */
    private $source;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Assert\Type("integer")
     * @Assert\Positive()
     */
    private $destination;

    /**
     * @var float
     * @Serializer\Type("float")
     * @Assert\Type("float")
     * @Assert\Positive
     */
    private $amount;

    /**
     * @return int
     */
    public function getSource(): int
    {
        return $this->source;
    }

    /**
     * @return int
     */
    public function getDestination(): int
    {
        return $this->destination;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }
}
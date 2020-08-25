<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 */
class Transaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var float
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @var float
     * @ORM\Column(type="float")
     */
    private $commissionPercent;

    /**
     * @var float
     * @ORM\Column(type="float")
     */
    private $commissionAmount;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $currency;

    /**
     * var DateTime
     * @ORM\Column(type="datetime", nullable=false, options={"default": "CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    /**
     * @var Wallet
     * @ORM\ManyToOne(targetEntity="Wallet", inversedBy="outcomingTransactions")
     */
    private $source;

    /**
     * @var Wallet
     * @ORM\ManyToOne(targetEntity="Wallet", inversedBy="incomingTransactions")
     */
    private $destination;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return Transaction
     */
    public function setAmount(float $amount): Transaction
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return float
     */
    public function getCommissionPercent(): float
    {
        return $this->commissionPercent;
    }

    /**
     * @param float $commissionPercent
     * @return Transaction
     */
    public function setCommissionPercent(float $commissionPercent): Transaction
    {
        $this->commissionPercent = $commissionPercent;
        return $this;
    }

    /**
     * @return float
     */
    public function getCommissionAmount(): float
    {
        return $this->commissionAmount;
    }

    /**
     * @param float $commissionAmount
     * @return Transaction
     */
    public function setCommissionAmount(float $commissionAmount): Transaction
    {
        $this->commissionAmount = $commissionAmount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Transaction
     */
    public function setCurrency(string $currency): Transaction
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return Transaction
     */
    public function setCreatedAt(DateTime $createdAt): Transaction
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Wallet
     */
    public function getSource(): Wallet
    {
        return $this->source;
    }

    /**
     * @param Wallet $source
     * @return Transaction
     */
    public function setSource(Wallet $source): Transaction
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return Wallet
     */
    public function getDestination(): Wallet
    {
        return $this->destination;
    }

    /**
     * @param Wallet $destination
     * @return Transaction
     */
    public function setDestination(Wallet $destination): Transaction
    {
        $this->destination = $destination;
        return $this;
    }
}

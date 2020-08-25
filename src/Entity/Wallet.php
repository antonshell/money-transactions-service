<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WalletRepository::class)
 */
class Wallet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var float
     * @ORM\Column(type="float")
     */
    private $balance;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $currency;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="wallets")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="source")
     *
     * @var Collection|Transaction[]
     */
    private $outcomingTransactions;

    /**
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="destination")
     *
     * @var Collection|Transaction[]
     */
    private $incomingTransactions;

    public function __construct() {
        $this->outcomingTransactions = new ArrayCollection();
        $this->incomingTransactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Wallet
     */
    public function setName(string $name): Wallet
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     * @return Wallet
     */
    public function setBalance(float $balance): Wallet
    {
        $this->balance = $balance;
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
     * @return Wallet
     */
    public function setCurrency(string $currency): Wallet
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Wallet
     */
    public function setUser(User $user): Wallet
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Transaction[]|Collection
     */
    public function getOutcomingTransactions(): Collection
    {
        return $this->outcomingTransactions;
    }

    /**
     * @return Transaction[]|Collection
     */
    public function getIncomingTransactions(): Collection
    {
        return $this->incomingTransactions;
    }
}

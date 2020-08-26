<?php

namespace App\Tests\DataFixtures\Controller;

use App\Enum\CurrencyEnum;
use App\Tests\Factory\EntityFactory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class DefaultFixture extends AbstractFixture
{
    public const REF_USER1 = 'user1';
    public const REF_USER2 = 'user2';

    public const EMAIL1 = 'user1@test.com';
    public const EMAIL2 = 'user2@test.com';

    public const PASSWORD1 = 'password1';
    public const PASSWORD2 = 'password2';

    public const REF_WALLET_BTC1 = 'btc1';
    public const REF_WALLET_ETH1 = 'eth1';
    public const REF_WALLET_BTC2 = 'btc2';
    public const REF_WALLET_ETH2 = 'eth2';

    private $config = [
        [
            'reference' => self::REF_USER1,
            'email' => self::EMAIL1,
            'password' => self::PASSWORD1,
            'wallets' => [
                [
                    'reference' => self::REF_WALLET_BTC1,
                    'balance' => 10,
                    'currency' => CurrencyEnum::BTC,
                ],
                [
                    'reference' => self::REF_WALLET_ETH1,
                    'balance' => 5,
                    'currency' => CurrencyEnum::ETH,
                ]
            ]
        ],
        [
            'reference' => self::REF_USER2,
            'email' => self::EMAIL2,
            'password' => self::PASSWORD2,
            'wallets' => [
                [
                    'reference' => self::REF_WALLET_BTC2,
                    'balance' => 7,
                    'currency' => CurrencyEnum::BTC,
                ],
                [
                    'reference' => self::REF_WALLET_ETH2,
                    'balance' => 4,
                    'currency' => CurrencyEnum::ETH,
                ]
            ]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $objectManager): void
    {
        $factory = new EntityFactory($objectManager);

        foreach ($this->config as $userRow){
            $user = $factory->createUser([
                'email' => $userRow['email'],
                'password' => password_hash($userRow['password'], PASSWORD_DEFAULT),
            ]);

            $this->setReference($userRow['reference'], $user);

            foreach ($userRow['wallets'] as $walletRow){
                $wallet = $factory->createWallet([
                    'balance' => $walletRow['balance'],
                    'currency' => $walletRow['currency'],
                    'user' => $user
                ]);

                $this->setReference($walletRow['reference'], $wallet);
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Portfolio;
use App\Entity\Post;
use App\Entity\PostTag;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\CurrencyEnum;

class EntityFactory extends AbstractFactory
{
    protected function addDefaults(array $data, array $defaults): array
    {
        return array_merge($defaults, $data);
    }

    public function createUser(array $data = []): User
    {
        $data = $this->addDefaults($data, [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $this->faker->password,
        ]);

        return $this->create(User::class, $data);
    }

    public function createWallet(array $data = []): Wallet
    {
        $data = $this->addDefaults($data, [
            'name' => $this->faker->name,
            'balance' => random_int(0, 10),
            'currency' => CurrencyEnum::BTC,
        ]);

        if (!array_key_exists('user', $data)) {
            $user = $this->createUser();
            $data['user'] = $user;
        }

        return $this->create(Wallet::class, $data);
    }
}

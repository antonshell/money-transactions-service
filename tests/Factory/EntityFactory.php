<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Portfolio;
use App\Entity\Post;
use App\Entity\PostTag;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Wallet;

class EntityFactory extends AbstractFactory
{
    protected function addDefaults(array $data, array $defaults): array
    {
        return array_merge($defaults, $data);
    }

    public function createUser(array $data = []): User
    {
        $data = $this->addDefaults($data, [
            'name' => '$this->faker->title',
            'email' => '$this->faker->titl',
            'password' => '$this->faker->word',
        ]);

        return $this->create(User::class, $data);
    }

    public function createWallet(array $data = []): Wallet
    {
        $data = $this->addDefaults($data, [
            'name' => '$this->faker->title',
            'balance' => '$this->faker->titl',
            'currency' => '$this->faker->word',
        ]);

        if (!array_key_exists('user', $data)) {
            $user = $this->createUser();
            $data['user'] = $user;
        }

        return $this->create(Wallet::class, $data);
    }
}

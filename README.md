# Software engineer test assignment - Paxful 

You need to implement a backend application that would allow users to transfer funds
between wallets. On every transaction, the system will need to take a commission of
1.5%.

# Requirements

- Create a relational database schema

- Upon application start, the database should be populated with sample data (e.g
several wallets that can be used to transfer funds between)

- The system should support two currency: BTC and ETH

- Create a REST endpoint that can be used to transfer funds

- Provide a docker-compose.yml that can be used to run your solution by just
doing ​docker-compose up

# Run with docker

1  . Clone repository:

```
git clone https://github.com/antonshell/paxful-wallets-api.git
```

2 . Run docker compose:

```
cd paxful-wallets-api
docker-compose up -d
```

3 . Load data:

```
docker-compose exec php-fpm php bin/console sample-data:load
```

4 . Health check:

```
curl --request GET \
  --url http://127.0.0.1:18680'
```

# Run locally (without docker)

1  . Clone repository

```
git clone https://github.com/antonshell/paxful-wallets-api.git
```

2 . Install dependencies

```
cd paxful-wallets-api
composer install
```

3 . Create database user

```
CREATE USER 'paxful'@'localhost' IDENTIFIED BY 'paxful';
GRANT ALL PRIVILEGES ON paxful.* TO 'paxful'@'localhost';
GRANT ALL PRIVILEGES ON paxful_test.* TO 'paxful'@'localhost';
```

4 . Set database settings

```
# nano .env
DATABASE_URL="mysql://paxful:paxful@127.0.0.1:3306/paxful?serverVersion=5.7"
```

```
# nano .env.test
DATABASE_URL="mysql://paxful:paxful@127.0.0.1:3306/paxful_test?serverVersion=5.7"
```

5 . Create databases:

```
php bin/console doctrine:database:create
php bin/console doctrine:database:create --env=test
```

6 . Apply migarations

```
php bin/console doctrine:migrations:migrate
php bin/console doctrine:migrations:migrate --env=test
```

5 . Run local dev server

```
php -S 127.0.0.1:8000 public/index.php
```

# Load sample data:

```
php bin/console sample-data:load
```

# Run tests:

```
./bin/phpunit
```

# Api reference

1 . Health check.

```
curl --request GET \
  --url http://127.0.0.1:8000/'
```

2 . Create transaction.

```
curl --request POST \
  --url http://127.0.0.1:8000/transaction \
  --header 'content-type: application/json' \
  --header 'password: user1' \
  --header 'username: user1@test.com' \
  --data '{
	"source": 5,
	"destination": 7,
	"amount": 1
}'
```
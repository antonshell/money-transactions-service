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

# Usage

1 . Health check.

```
curl --request GET \
  --url http://127.0.0.1:18680/
```

2 . Create transaction from user 1 to user 2

```
curl --request POST \
  --url http://127.0.0.1:18680/transaction \
  --header 'content-type: application/json' \
  --header 'password: user1' \
  --header 'username: user1@test.com' \
  --data '{
	"source": 1,
	"destination": 3,
	"amount": 1
}'
```

2 . Create transaction from user 2 to user 1

```
curl --request POST \
  --url http://127.0.0.1:18680/transaction \
  --header 'content-type: application/json' \
  --header 'password: user2' \
  --header 'username: user2@test.com' \
  --data '{
	"source": 3,
	"destination": 1,
	"amount": 1
}'
```

# DB connection

Host: localhost
Database: paxful
User: paxful
Password: paxful

# Tests

```
docker-compose exec php-fpm ./bin/phpunit
```

# Disclaimer

- I have problems with running tests in docker container, trying to resolve.
For now, I run tests locally. There are only functional tests. I would need more time for unit tests implementation.

- I use float for price. I think, it's not the best approach, but I think, it's out of scope for this task.
I would use some specific doctrine type, or some php library for money processing.

- For now there are no access tokens, I use email/password for authentication


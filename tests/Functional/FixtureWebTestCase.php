<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FixtureWebTestCase extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    private $referenceRepository;

    /**
     * @var array
     */
    private $fixturesEntityManagers = [];

    /**
     * @var array
     */
    protected $fixtureInstances = [];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected $client;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->em = $this->loadEntityManager(EntityManagerInterface::class);
        $this->referenceRepository = $this->loadFixtures($this->em, $this->getFixtures())->getReferenceRepository();
    }

    /**
     * Get array of fixtures FQCNs.
     *
     * @return string[]
     */
    abstract protected function getFixtures(): array;

    protected function getReference(string $name)
    {
        return $this->referenceRepository->getReference($name);
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function loadEntityManager(string $serviceName): EntityManagerInterface
    {
        $em = self::$container->get($serviceName);

        if (!$em instanceof EntityManagerInterface) {
            $eMessage = sprintf('%s is not instance of %s', $serviceName, EntityManagerInterface::class);
            throw new \InvalidArgumentException($eMessage);
        }

        if (!$em->getConnection()->isTransactionActive()) {
            $em->beginTransaction();
        }

        return $em;
    }

    protected function getFixtureEntityManager(AbstractFixture $fixture, EntityManagerInterface $defaultEm): EntityManagerInterface
    {
        $fixtureClass = get_class($fixture);
        $entityManagerName = $this->fixturesEntityManagers[$fixtureClass];

        $em = self::$container->has($entityManagerName)
            ? $this->loadEntityManager($entityManagerName)
            : $defaultEm;

        return $em;
    }

    public function loadFixtures(EntityManagerInterface $defaultEm, array $fixtures): ORMExecutor
    {
        $loader = new Loader();

        foreach ($fixtures as $fixtureEntityManager => $fixture) {
            $this->loadFixtureClass($fixture, $fixtureEntityManager, $loader);
        }

        $connection = $this->em->getConnection();
        if ($connection->getDriver() instanceof \Doctrine\DBAL\Driver\PDOMySql\Driver) {
            $connection->executeUpdate('SET foreign_key_checks = 0;');
        }

        $purger = new ORMPurger();
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);

        //$executor = new ORMExecutor($defaultEm);
        $executor = new ORMExecutor($defaultEm, $purger);
        $executor->purge();

        foreach ($loader->getFixtures() as $fixture) {
            $em = $this->getFixtureEntityManager($fixture, $defaultEm);
            $executor->load($em, $fixture);
        }

        if ($connection->getDriver() instanceof \Doctrine\DBAL\Driver\PDOMySql\Driver) {
            $em->getConnection()->executeUpdate('SET foreign_key_checks = 1;');
        }

        return $executor;
    }

    /**
     * @param mixed $managerName
     */
    private function loadFixtureClass(string $fixtureName, $managerName, Loader $loader): void
    {
        $fixtureInstance = new $fixtureName();
        $this->fixturesEntityManagers[$fixtureName] = $managerName;
        $this->fixtureInstances[$fixtureName] = $fixtureInstance;

        if (!$loader->hasFixture($fixtureInstance)) {
            $loader->addFixture($fixtureInstance);
        }

        if ($fixtureInstance instanceof DependentFixtureInterface) {
            foreach ($fixtureInstance->getDependencies() as $fixtureEntityManager => $dependency) {
                $this->loadFixtureClass($dependency, $fixtureEntityManager, $loader);
            }
        }
    }

    protected function getFixtureInstance(string $fixtureName): ?AbstractFixture
    {
        return $this->fixtureInstances[$fixtureName] ?? null;
    }
}

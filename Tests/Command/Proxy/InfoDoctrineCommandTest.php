<?php

namespace Command\Proxy;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\InfoDoctrineCommand;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

class InfoDoctrineCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $kernel = $this->setupKernelMocks();

        $application = new Application($kernel);
        $application->add(new InfoDoctrineCommand());

        $command = $application->find('doctrine:mapping:info');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array_merge(['command' => $command->getName()])
        );

        $this->assertStringContainsString(
            'You do not have any mapped Doctrine ORM entities according to the current configuration.',
            $commandTester->getDisplay()
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|Kernel
     */
    private function setupKernelMocks()
    {
        $configuration = new Configuration();
        $configuration->setMetadataDriverImpl(new MappingDriverChain());

        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $manager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $manager->method('getConnection')->willReturn($connection);
        $manager->method('getConfiguration')->willReturn($configuration);

        $registry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $registry->method('getManager')->willReturn($manager);

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->method('get')->willReturn($registry);

        $kernel = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();
        $kernel->method('getBundles')->willReturn([]);
        $kernel->method('getContainer')->willReturn($container);

        return $kernel;
    }
}
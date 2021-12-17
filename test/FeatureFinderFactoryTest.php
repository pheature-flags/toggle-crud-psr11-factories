<?php

namespace Pheature\Test\Crud\Psr11\Toggle;

use Doctrine\DBAL\Connection;
use Pheature\Core\Toggle\Read\ChainFeatureFinder;
use Pheature\Core\Toggle\Read\ChainToggleStrategyFactory;
use Pheature\Core\Toggle\Read\FeatureFinder;
use Pheature\Core\Toggle\Read\SegmentFactory;
use Pheature\Core\Toggle\Read\ToggleStrategyFactory;
use Pheature\Crud\Psr11\Toggle\FeatureFinderFactory;
use Pheature\Crud\Psr11\Toggle\ToggleConfig;
use Pheature\Dbal\Toggle\Read\DbalFeatureFinder;
use Pheature\InMemory\Toggle\InMemoryFeatureFinder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use InvalidArgumentException;

class FeatureFinderFactoryTest extends TestCase
{
    public function testItShouldThrowAnExceptionWhenItCantCreateAFeatureFinder(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected one of: "inmemory", "dbal", "chain". Got: "some_other"');
        $container = $this->createMock(ContainerInterface::class);
        $chainStrategyFactory = new ChainToggleStrategyFactory(
            $this->createMock(SegmentFactory::class),
            $this->createMock(ToggleStrategyFactory::class),
        );
        $toggleConfig = new ToggleConfig(['api_enabled' => false, 'api_prefix' => '', 'driver' => 'some_other']);

        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([ToggleConfig::class], [ChainToggleStrategyFactory::class], [Connection::class])
            ->willReturnOnConsecutiveCalls($toggleConfig, $chainStrategyFactory, null);

        $featureFinderFactory = new FeatureFinderFactory();

        $featureFinderFactory->__invoke($container);
    }

    public function testItShouldCreateADBalFeatureFinderFromInvokable(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $toggleConfig = new ToggleConfig(['api_enabled' => false, 'api_prefix' => '', 'driver' => 'dbal']);
        $chainStrategyFactory = new ChainToggleStrategyFactory(
            $this->createMock(SegmentFactory::class),
            $this->createMock(ToggleStrategyFactory::class),
        );
        $connection = $this->createMock(Connection::class);

        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([ToggleConfig::class], [ChainToggleStrategyFactory::class], [Connection::class])
            ->willReturnOnConsecutiveCalls($toggleConfig, $chainStrategyFactory, $connection);

        $featureFinderFactory = new FeatureFinderFactory();

        $finder = $featureFinderFactory->__invoke($container);
        $this->assertInstanceOf(DbalFeatureFinder::class, $finder);
    }

    public function testItShouldCreateAnInMemoryFeatureFinderFromInvokable(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $toggleConfig = new ToggleConfig(['api_enabled' => false, 'api_prefix' => '', 'driver' => 'inmemory']);
        $chainStrategyFactory = new ChainToggleStrategyFactory(
            $this->createMock(SegmentFactory::class),
            $this->createMock(ToggleStrategyFactory::class),
        );

        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([ToggleConfig::class], [ChainToggleStrategyFactory::class])
            ->willReturnOnConsecutiveCalls($toggleConfig, $chainStrategyFactory);

        $featureFinderFactory = new FeatureFinderFactory();

        $finder = $featureFinderFactory->__invoke($container);
        $this->assertInstanceOf(InMemoryFeatureFinder::class, $finder);
    }

    public function testItShouldCreateAChainFeatureFinderFromInvokable(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $toggleConfig = new ToggleConfig(['api_enabled' => false, 'api_prefix' => '', 'driver' => 'chain', 'driver_options' => ['dbal', 'inmemory']]);
        $chainStrategyFactory = new ChainToggleStrategyFactory(
            $this->createMock(SegmentFactory::class),
            $this->createMock(ToggleStrategyFactory::class),
        );
        $connection = $this->createMock(Connection::class);

        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([ToggleConfig::class], [ChainToggleStrategyFactory::class], [Connection::class])
            ->willReturnOnConsecutiveCalls($toggleConfig, $chainStrategyFactory, $connection);

        $featureFinderFactory = new FeatureFinderFactory();

        $finder = $featureFinderFactory->__invoke($container);
        $this->assertInstanceOf(ChainFeatureFinder::class, $finder);
    }

    public function testItShouldCreateADBalFeatureFinderFromCreate(): void
    {
        $toggleConfig = new ToggleConfig(['api_enabled' => false, 'api_prefix' => '', 'driver' => 'dbal']);
        $chainStrategyFactory = new ChainToggleStrategyFactory(
            $this->createMock(SegmentFactory::class),
            $this->createMock(ToggleStrategyFactory::class),
        );
        $connection = $this->createMock(Connection::class);
        $featureFinder = FeatureFinderFactory::create($toggleConfig, $chainStrategyFactory, $connection);
        $this->assertInstanceOf(FeatureFinder::class, $featureFinder);
    }

    public function testItShouldThrowExceptionWithInvalidFeatureFinderFromCreate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Valid driver required');
        $toggleConfig = new ToggleConfig(['api_enabled' => false, 'api_prefix' => '', 'driver' => 'inmemory']);
        $reWriteConfig = fn () => $this->driver = 'some_other';
        $reWriteConfig->call($toggleConfig);
        $chainStrategyFactory = new ChainToggleStrategyFactory(
            $this->createMock(SegmentFactory::class),
            $this->createMock(ToggleStrategyFactory::class),
        );
        $connection = $this->createMock(Connection::class);
        FeatureFinderFactory::create($toggleConfig, $chainStrategyFactory, $connection);
    }

    public function testItShouldCreateInMemoryFeatureFinderFromCreate(): void
    {
        $toggleConfig = new ToggleConfig(['api_enabled' => false, 'api_prefix' => '', 'driver' => 'inmemory']);
        $chainStrategyFactory = new ChainToggleStrategyFactory(
            $this->createMock(SegmentFactory::class),
            $this->createMock(ToggleStrategyFactory::class),
        );
        $connection = null;
        $featureFinder = FeatureFinderFactory::create($toggleConfig, $chainStrategyFactory, $connection);
        $this->assertInstanceOf(FeatureFinder::class, $featureFinder);
    }

    public function testItShouldCreateChainFeatureFinderFromCreate(): void
    {
        $toggleConfig = new ToggleConfig(['api_enabled' => false, 'api_prefix' => '', 'driver' => 'chain', 'driver_options' => ['inmemory']]);
        $chainStrategyFactory = new ChainToggleStrategyFactory(
            $this->createMock(SegmentFactory::class),
            $this->createMock(ToggleStrategyFactory::class),
        );
        $connection = null;
        $featureFinder = FeatureFinderFactory::create($toggleConfig, $chainStrategyFactory, $connection);
        $this->assertInstanceOf(FeatureFinder::class, $featureFinder);
    }

}

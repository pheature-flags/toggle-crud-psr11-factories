<?php

declare(strict_types=1);

namespace Pheature\Test\Crud\Psr11\Toggle;

use Pheature\Core\Toggle\Read\ChainToggleStrategyFactory;
use Pheature\Model\Toggle\SegmentFactory;
use Pheature\Crud\Psr11\Toggle\ChainToggleStrategyFactoryFactory;
use Pheature\Crud\Psr11\Toggle\ToggleConfig;
use Pheature\Model\Toggle\StrategyFactory;
use Pheature\Test\Crud\Psr11\Toggle\Fixtures\PheatureFlagsConfig;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ChainToggleStrategyFactoryFactoryTest extends TestCase
{
    public function testItShouldCreateInstancesOfChainToggleStrategyFactory(): void
    {
        $segmentFactory = new SegmentFactory();
        $strategyFactory = new StrategyFactory();
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(
                [SegmentFactory::class],
                [ToggleConfig::class],
                ['enable_by_matching_segment'],
                ['enable_by_matching_identity_id']
            )
            ->willReturnOnConsecutiveCalls(
                $segmentFactory,
                new ToggleConfig(PheatureFlagsConfig::createDefault()->build()),
                $strategyFactory,
                $strategyFactory,
            );

        $factory = new ChainToggleStrategyFactoryFactory();
        $chainToggleStrategyFactory = $factory->__invoke($container);
        $this->assertInstanceOf(ChainToggleStrategyFactory::class, $chainToggleStrategyFactory);
    }

    public function testItShouldCreateInstancesOfChainToggleStrategyFactoryStatically(): void
    {
        $segmentFactory = new SegmentFactory();
        $strategyFactory = new StrategyFactory();

        $chainToggleStrategyFactory = ChainToggleStrategyFactoryFactory::create($segmentFactory, $strategyFactory, $strategyFactory);
        $this->assertInstanceOf(ChainToggleStrategyFactory::class, $chainToggleStrategyFactory);
    }
}

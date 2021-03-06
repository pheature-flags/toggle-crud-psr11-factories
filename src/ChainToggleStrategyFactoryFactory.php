<?php

declare(strict_types=1);

namespace Pheature\Crud\Psr11\Toggle;

use Pheature\Core\Toggle\Read\ToggleStrategyFactory;
use Pheature\Model\Toggle\SegmentFactory;
use Psr\Container\ContainerInterface;
use Pheature\Core\Toggle\Read\ChainToggleStrategyFactory;

class ChainToggleStrategyFactoryFactory
{
    public function __invoke(ContainerInterface $container): ChainToggleStrategyFactory
    {
        /** @var SegmentFactory $segmentFactory */
        $segmentFactory = $container->get(SegmentFactory::class);
        /** @var ToggleConfig $toggleConfig */
        $toggleConfig = $container->get(ToggleConfig::class);

        return self::create(
            $segmentFactory,
            ...array_map(
                static function (array $strategyType) use ($container) {
                    /** @var ToggleStrategyFactory $toggleStrategyFactory */
                    $toggleStrategyFactory = $container->get($strategyType['type']);

                    return $toggleStrategyFactory;
                },
                $toggleConfig->strategyTypes()
            )
        );
    }

    public static function create(
        SegmentFactory $segmentFactory,
        ToggleStrategyFactory ...$toggleStrategyFactories
    ): ChainToggleStrategyFactory {
        return new ChainToggleStrategyFactory($segmentFactory, ...$toggleStrategyFactories);
    }
}

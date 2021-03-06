<?php

declare(strict_types=1);

namespace Pheature\Crud\Psr11\Toggle;

use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use Pheature\Core\Toggle\Write\ChainFeatureRepository;
use Pheature\Core\Toggle\Write\FeatureRepository;
use Pheature\Dbal\Toggle\Write\DbalFeatureRepository;
use Pheature\InMemory\Toggle\InMemoryFeatureRepository;
use Psr\Container\ContainerInterface;

final class FeatureRepositoryFactory
{
    public function __invoke(ContainerInterface $container): FeatureRepository
    {
        /** @var ToggleConfig $config */
        $config = $container->get(ToggleConfig::class);
        /** @var ?Connection $connection */
        $connection = $container->get(Connection::class);

        return self::create($config, $connection);
    }

    public static function create(ToggleConfig $config, ?Connection $connection): FeatureRepository
    {
        $driver = $config->driver();

        if (ToggleConfig::DRIVER_IN_MEMORY === $driver) {
            return new InMemoryFeatureRepository();
        }

        if (ToggleConfig::DRIVER_DBAL === $driver) {
            /** @var Connection $connection */
            return new DbalFeatureRepository($connection);
        }

        if (ToggleConfig::DRIVER_CHAIN === $driver) {
            $drivers = [];
            foreach ($config->driverOptions() as $driverOption) {
                $drivers[] = self::create(self::getDriverConfig($driverOption, $config), $connection);
            }

            return new ChainFeatureRepository(...$drivers);
        }

        throw new InvalidArgumentException('Valid driver required');
    }

    private static function getDriverConfig(string $driverOption, ToggleConfig $config): ToggleConfig
    {
        return new ToggleConfig([
            'driver' => $driverOption,
            'api_enabled' => $config->apiEnabled(),
            'api_prefix' => $config->apiPrefix(),
            'toggles' => $config->toggles(),
        ]);
    }
}

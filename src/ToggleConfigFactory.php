<?php

declare(strict_types=1);

namespace Pheature\Crud\Psr11\Toggle;

use ArrayObject;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

final class ToggleConfigFactory
{
    private const MISSING_CONFIG = '"pheature_flags" configuration not found in container';

    public function __invoke(ContainerInterface $container): ToggleConfig
    {
        /** @var array<string, mixed>|ArrayObject<string, mixed> $config */
        $config = $container->get('config');
        if ($config instanceof ArrayObject) {
            $config = $config->getArrayCopy();
        }

        Assert::keyExists($config, 'pheature_flags', self::MISSING_CONFIG);
        Assert::isArray($config['pheature_flags'], self::MISSING_CONFIG);

        /** @var array<string, mixed> $pheatureConfig */
        $pheatureConfig = $config['pheature_flags'];

        return new ToggleConfig($pheatureConfig);
    }
}

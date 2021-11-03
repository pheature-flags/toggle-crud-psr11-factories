<?php

declare(strict_types=1);

namespace Pheature\Crud\Psr11\Toggle;

use Webmozart\Assert\Assert;

final class ToggleConfig
{
    public const DRIVER_IN_MEMORY = 'inmemory';
    public const DRIVER_DBAL = 'dbal';
    public const DRIVER_CHAIN = 'chain';
    private const VALID_DRIVERS = [
        self::DRIVER_IN_MEMORY,
        self::DRIVER_DBAL,
        self::DRIVER_CHAIN,
    ];
    private const VALID_DRIVER_OPTIONS = [
        self::DRIVER_IN_MEMORY,
        self::DRIVER_DBAL,
    ];

    private string $driver;
    /** @var array<string> */
    private $driverOptions;
    private bool $apiEnabled;
    private string $apiPrefix;
    /** @var array<array<string, string>> */
    private array $strategyTypes;
    /** @var array<array<string, string>> */
    private array $segmentTypes;
    /** @var array<string, mixed> */
    private array $toggles;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->assertDriver($config);
        Assert::keyExists($config, 'api_enabled');
        Assert::boolean($config['api_enabled']);
        Assert::keyExists($config, 'api_prefix');
        Assert::string($config['api_prefix']);
        Assert::keyExists($config, 'driver');
        Assert::string($config['driver']);

        $this->apiEnabled = $config['api_enabled'];
        $this->apiPrefix = $config['api_prefix'];
        $this->driver = $config['driver'];
        /** @var array<string> $driverOptions */
        $driverOptions = $config['driver_options'] ?? [];
        $this->driverOptions = $driverOptions;

        $this->strategyTypes = [];
        $this->segmentTypes = [];
        $this->toggles = [];

        if (array_key_exists('strategy_types', $config)) {
            Assert::isArray($config['strategy_types']);
            /** @var array<array<string, string>> $strategyTypes */
            $strategyTypes = $config['strategy_types'];
            $this->strategyTypes = $strategyTypes;
        }

        if (array_key_exists('segment_types', $config)) {
            Assert::isArray($config['segment_types']);
            /** @var array<array<string, string>> $segmentTypes */
            $segmentTypes = $config['segment_types'];
            $this->segmentTypes = $segmentTypes;
        }

        if (array_key_exists('toggles', $config)) {
            Assert::isArray($config['toggles']);
            /** @var array<string, mixed> $toggles */
            $toggles = $config['toggles'];
            $this->toggles = $toggles;
        }
    }

    /**
     * @param array<string, mixed> $config
     * @return void
     */
    private function assertDriver(array $config): void
    {
        Assert::keyExists($config, 'driver');
        Assert::string($config['driver']);
        Assert::inArray($config['driver'], self::VALID_DRIVERS);

        $this->assertDriverOptions($config);
    }

    /**
     * @param array<string, mixed> $config
     * @return void
     */
    private function assertDriverOptions(array $config): void
    {
        if (self::DRIVER_CHAIN === $config['driver']) {
            Assert::keyExists($config, 'driver_options');
            Assert::isArray($config['driver_options']);
            Assert::notEmpty($config['driver_options']);
            Assert::allInArray($config['driver_options'], self::VALID_DRIVER_OPTIONS);
        }
    }

    public function apiEnabled(): bool
    {
        return $this->apiEnabled;
    }

    public function apiPrefix(): string
    {
        return $this->apiPrefix;
    }

    public function driver(): string
    {
        return $this->driver;
    }

    /**
     * @return array<string>
     */
    public function driverOptions(): array
    {
        return $this->driverOptions;
    }

    /**
     * @return array<array<string, string>>
     */
    public function strategyTypes(): array
    {
        return $this->strategyTypes;
    }

    /**
     * @return array<array<string, string>>
     */
    public function segmentTypes(): array
    {
        return $this->segmentTypes;
    }

    /**
     * @return array<string, mixed>
     */
    public function toggles(): array
    {
        return $this->toggles;
    }
}

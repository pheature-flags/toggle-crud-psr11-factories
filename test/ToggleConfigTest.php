<?php

declare(strict_types=1);

namespace Pheature\Test\Crud\Psr11\Toggle;

use Generator;
use Pheature\Crud\Psr11\Toggle\ToggleConfig;
use Pheature\Test\Crud\Psr11\Toggle\Fixtures\PheatureFlagsConfig;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

final class ToggleConfigTest extends TestCase
{
    public function testItThrowsExceptionIfDriverIsInvalid(): void
    {
        $config = PheatureFlagsConfig::createDefault()
            ->withDriver('invalid_driver')
            ->build();

        $this->expectException(InvalidArgumentException::class);

        new ToggleConfig($config);
    }

    public function wrongDriverOptionsDataProvider(): Generator
    {
        yield 'empty driver_options' => [
            PheatureFlagsConfig::createDefault()
                ->withDriver('chain')
                ->withDriverOptions([])
                ->build()
        ];

        yield 'invalid driver_options' => [
            PheatureFlagsConfig::createDefault()
                ->withDriver('chain')
                ->withDriverOptions(['invalid_option'])
                ->build()
        ];
    }

    /** @dataProvider wrongDriverOptionsDataProvider */
    public function testItThrowsExceptionIfChainDriverWithEmptyDriverOptions(array $config): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ToggleConfig($config);
    }

    public function validDriversProvider(): Generator
    {
        yield 'inmemory driver' => [
            'config' => PheatureFlagsConfig::createDefault()
                ->withDriver('inmemory')
                ->build(),
            'driver' => 'inmemory',
            'driver_options' => [],
        ];
        yield 'dbal driver' => [
            'config' => PheatureFlagsConfig::createDefault()
                ->withDriver('dbal')
                ->build(),
            'driver' => 'dbal',
            'driver_options' => [],
        ];
        yield 'chain only inmemory' => [
            'config' => PheatureFlagsConfig::createDefault()
                ->withDriver('chain')
                ->withDriverOptions(['inmemory'])
                ->build(),
            'driver' => 'chain',
            'driver_options' => ['inmemory'],
        ];
        yield 'chain only dbal' => [
            'config' => PheatureFlagsConfig::createDefault()
                ->withDriver('chain')
                ->withDriverOptions(['dbal'])
                ->build(),
            'driver' => 'chain',
            'driver_options' => ['dbal'],
        ];
        yield 'chain inmemory and dbal' => [
            'config' => PheatureFlagsConfig::createDefault()
                ->withDriver('chain')
                ->withDriverOptions(['inmemory', 'dbal'])
                ->build(),
            'driver' => 'chain',
            'driver_options' => ['inmemory', 'dbal'],
        ];
    }

    /** @dataProvider validDriversProvider */
    public function testItIsCreatedWithValidDrivers(
        array $config,
        string $expectedDriver,
        array $expectedDriverOptions
    ): void {
        $toggleConfig = new ToggleConfig($config);

        $this->assertSame($expectedDriver, $toggleConfig->driver());
        $this->assertSame($expectedDriverOptions, $toggleConfig->driverOptions());
    }

    public function wrongApiConfigurationProvider(): Generator
    {
        yield 'api_enabled key does not exist' => [
           PheatureFlagsConfig::createDefault()
            ->withoutKey('api_enabled')
            ->build()
        ];

        yield 'api_enabled is not boolean' => [
            PheatureFlagsConfig::createDefault()
                ->with('api_enabled', 'wrong_value')
                ->build()
        ];

        yield 'api_prefix key does not exist' => [
            PheatureFlagsConfig::createDefault()
                ->withoutKey('api_prefix')
                ->build()
        ];

        yield 'api_prefix is not string' => [
            PheatureFlagsConfig::createDefault()
                ->with('api_prefix', true)
                ->build()
        ];
    }

    /** @dataProvider wrongApiConfigurationProvider */
    public function testItThrowsExceptionIfWrongApiConfiguration(array $config): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ToggleConfig($config);
    }

    public function testItThrowsExceptionIfWrongStrategyTypesConfiguration(): void
    {
        $config = PheatureFlagsConfig::createDefault()
            ->with('strategy_types', 'non_array_value')
            ->build();

        $this->expectException(InvalidArgumentException::class);

        new ToggleConfig($config);
    }

    public function testItThrowsExceptionIfWrongSegmentTypesConfiguration(): void
    {
        $config = PheatureFlagsConfig::createDefault()
            ->with('segment_types', 'non_array_value')
            ->build();

        $this->expectException(InvalidArgumentException::class);

        new ToggleConfig($config);
    }

    public function testItThrowsExceptionIfWrongTogglesConfiguration(): void
    {
        $config = PheatureFlagsConfig::createDefault()
            ->with('toggles', 'non_array_value')
            ->build();

        $this->expectException(InvalidArgumentException::class);

        new ToggleConfig($config);
    }

    public function driverOptionsForNoChainDriverProvider(): Generator
    {
        yield 'inmemory' => [
            PheatureFlagsConfig::createDefault()
                ->withDriver('inmemory')
                ->withDriverOptions(['inmemory', 'dbal'])
                ->build()
        ];

        yield 'dbal' => [
            PheatureFlagsConfig::createDefault()
                ->withDriver('dbal')
                ->withDriverOptions(['inmemory', 'dbal'])
                ->build()
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\CommissionTask\Services;

use App\CommissionTask\Enums\ConfigName;
use Symfony\Component\Dotenv\Dotenv;

class ConfigService
{
    /**
     * @var array<string, int|float|string>
     */
    private array $configuration;

    private static self $instance;

    private function __construct()
    {
        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../../.env', overrideExistingVars: true);

        $this->loadConfiguration();
    }

    public static function getInstance(bool $reload = false): self
    {
        if (!isset(self::$instance) || $reload) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get(ConfigName $configName): string|int|float
    {
        return $this->configuration[$configName->value];
    }

    public function loadConfiguration(): void
    {
        foreach (ConfigName::cases() as $configName) {
            $value = $_ENV[$configName->value];
            if ($value === false || !(is_string($value) || is_int($value) || is_float($value))) {
                throw new \RuntimeException("Missing environment variable: {$configName->value}");
            }

            $this->configuration[$configName->value] = $value;
        }
    }
}

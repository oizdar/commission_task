<?php

declare(strict_types=1);

namespace App\CommissionTask\Services;

use App\CommissionTask\Enums\ConfigName;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Money\Exchange;
use Money\Exchange\FixedExchange;
use Money\Exchange\ReversedCurrenciesExchange;

class ExchangeRatesClient
{
    private Client $client;
    private string $base;

    /**
     * @var array<non-empty-string, numeric-string>|null
     */
    private ?array $rates = null;
    private ?\DateTimeImmutable $latestUpdateDate = null;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => ConfigService::getInstance()->get(ConfigName::ExchangeRatesApiUrl),
        ]);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws GuzzleException
     */
    public function getRates(): Exchange
    {
        if (empty($this->rates)
            || !isset($this->base)
            || $this->latestUpdateDate?->format('Y-m-d') !== (new \DateTimeImmutable('now'))->format('Y-m-d')
        ) {
            $this->fetchRates();
        }
        $expectedWithdrawCommissionCurrency = ConfigService::getInstance()->get(ConfigName::WithdrawCommissionCurrency);
        if ($this->base !== $expectedWithdrawCommissionCurrency) {
            throw new \RuntimeException('Base currency is not expected: '.$expectedWithdrawCommissionCurrency);
        }

        return new ReversedCurrenciesExchange(new FixedExchange(
            array_map(
                fn (array $rates) => array_map(fn ($rate) => (string) $rate, $rates),
                $this->validateKeys($this->base, $this->rates ?? [])
            )
        ));
    }

    /**
     * @param array<non-empty-string, numeric-string> $rates
     *
     * @return array<non-empty-string, array<non-empty-string, numeric-string>>
     */
    private function validateKeys(string $base, array $rates): array
    {
        if ($base === '') {
            throw new \InvalidArgumentException('Base currency key cannot be empty.');
        }

        $validatedRates = [];
        foreach ($rates as $key => $value) {
            $key = (string) $key;
            if ($key === '') {
                throw new \InvalidArgumentException('Currency key cannot be empty.');
            }
            $validatedRates[$key] = $value;
        }

        return [$base => $validatedRates];
    }

    /**
     * @throws \DateMalformedStringException
     * @throws GuzzleException
     */
    private function fetchRates(): void
    {
        $response = $this->client->get('tasks/api/currency-exchange-rates');

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Failed to fetch exchange rates');
        }

        /**
         * @var array{base: non-empty-string, rates: array<non-empty-string, numeric-string>, date: string} $result
         */
        $result = json_decode($response->getBody()->getContents(), true);

        $this->rates = $result['rates'] ?? [];
        $this->base = $result['base'];
        $this->latestUpdateDate = new \DateTimeImmutable($result['date']);
    }

    public function getLatestUpdateDate(): ?\DateTimeImmutable
    {
        return $this->latestUpdateDate;
    }

    public function getBaseCurrency(): ?string
    {
        return $this->base;
    }
}

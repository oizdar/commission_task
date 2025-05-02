<?php

declare(strict_types=1);

namespace App\CommissionTask\Services;

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
            'base_uri' => 'https://developers.paysera.com/',
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

        if ($this->base !== CommissionsCalculator::WITHDRAW_COMMISSION_CURRENCY) {
            throw new \RuntimeException('Base currency is not EUR');
        }

        return new ReversedCurrenciesExchange(new FixedExchange([
            $this->base => array_map(fn ($item) => (string) $item, $this->rates ?? []),
        ]));
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
         * @var array{base: string, rates: array<non-empty-string, numeric-string>, date: string}
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

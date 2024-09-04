<?php

declare(strict_types = 1);

namespace App\ExchangeRates\services;

use App\ExchangeRates\repositories\ExchangeRateRepository;
use Illuminate\Cache\Repository;

class ExchangeRateRetrievalService
{
    public const DATES_CACHE_KEY = 'dates';
    public const CURRENCIES_CACHE_KEY = 'currencies';

    public function __construct(
        private readonly ExchangeRateRepository $exchangeRateRepository,
        private readonly Repository $cacheRepository
    )
    {
    }

    public function getExchangeRateCurrencies(): array
    {
        $currencies = $this->cacheRepository->get(self::CURRENCIES_CACHE_KEY);

        if (!$currencies) {
            $currencies = $this->exchangeRateRepository->getCurrencyList();
            $this->cacheRepository->put(self::CURRENCIES_CACHE_KEY, $currencies);
        }

        return $currencies;
    }

    public function getExchangeRateDates(): array
    {
        $dates = $this->cacheRepository->get(self::DATES_CACHE_KEY);

        if (!$dates) {
            $dates = $this->exchangeRateRepository->getDatesList();
            $this->cacheRepository->put(self::DATES_CACHE_KEY, $dates);
        }

        return $dates;
    }

    public function getExchangeRates(string $startDate, string $endDate, array $currencies) : array
    {
        $currencyRates = $this->exchangeRateRepository->getRates($startDate, $endDate, $currencies);

        // Grouping and structuring data for easier use in the front end
        $groupedData = $currencyRates->groupBy('currency')->map(function ($rates) {
            return $rates->pluck('rate', 'date');
        });

        return [
            'dates' => $currencyRates->pluck('date')->unique()->values(),
            'rates' => $groupedData,
        ];
    }
}

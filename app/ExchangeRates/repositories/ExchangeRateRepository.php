<?php

namespace App\ExchangeRates\repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExchangeRateRepository
{
    private const EXCHANGE_RATES_TABLE = 'exchange_rates';

    public function insert(array $data): void
    {
        DB::table(self::EXCHANGE_RATES_TABLE)->insert($data);
    }

    public function getRates(string $startDate, string $endDate, array $currencies): Collection
    {
        $query = DB::table(self::EXCHANGE_RATES_TABLE)->whereBetween('date', [$startDate, $endDate]);

        if ($currencies) {
            $query->whereIn('currency', $currencies);
        }

        return $query->orderBy('currency')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getCurrencyList(): array
    {
        return DB::table(self::EXCHANGE_RATES_TABLE)
            ->orderBy('currency')
            ->distinct()
            ->pluck('currency')
            ->toArray();
    }

    public function getDatesList(): array
    {
        return DB::table(self::EXCHANGE_RATES_TABLE)
            ->orderBy('date', 'desc')
            ->distinct()
            ->pluck('date')
            ->toArray();
    }
}

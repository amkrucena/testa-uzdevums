<?php

namespace App\ExchangeRates\repositories;

use Illuminate\Support\Facades\DB;

class ExchangeRateRepository
{
    private const EXCHANGE_RATES_TABLE = 'exchange_rates';

    public function insert(array $data): void
    {
        DB::table(self::EXCHANGE_RATES_TABLE)->insert($data);
    }
}

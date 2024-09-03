<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\ExchangeRates\services\ExchangeRateService;
use Illuminate\Console\Command;

class GetExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-exchange-rates {days?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
       $results =  app(ExchangeRateService::class)->getExchangeRatesForDays((int)($this->argument('days') ?? 1));

       $this->info('Dates imported:');
       foreach ($results as $date) {
           $this->info($date);
       }
    }
}

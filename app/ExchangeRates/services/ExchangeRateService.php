<?php

declare(strict_types = 1);

namespace App\ExchangeRates\services;

use App\ExchangeRates\exceptions\MissingExchangeRateUrlException;
use App\ExchangeRates\exceptions\WrongDateException;
use App\ExchangeRates\repositories\ExchangeRateRepository;
use App\Wrappers\XmlReaderWrapper;
use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Http\Client\Factory;
use Illuminate\Log\LogManager;
use Throwable;

class ExchangeRateService
{
    private const DATA_FALLOFF_DATE = '19990104';
    private string $exchangeRateUrl;

    /**
     * @throws \Exception
     */
    public function __construct(
        private readonly Factory                 $factory,
        private readonly XmlReaderWrapper        $xmlReaderWrapper,
        private readonly ExchangeRateRepository  $exchangeRateRepository,
        private readonly LogManager              $logManager,
        private readonly Repository              $config,
    )
    {
        $this->exchangeRateUrl = $this->config->get('bank.exchange_rate_url') ?? '';

        if (empty($this->exchangeRateUrl)) {
            throw new MissingExchangeRateUrlException('Exchange rate url is empty');
        }
    }

    public function getExchangeRatesForDays(int $days = 1): array
    {
        $results = [];

        $dayCount = 0;
        $i=0;
        while ($i < $days) {
            $date = $this->getWeekdayDate($dayCount);
            try {
                $this->getExchangeRates($date);
                $results[] = $date;
            } catch (Throwable) {
                // move days along because this was a holiday.
                // Not using static holiday day checks, because they differ each year
                $dayCount++;
                continue;
            }

            $dayCount++;
            $i++;
        }

        return $results;
    }

    /**
     * Search for weekdays, because there is no currency rate data for weekends.
     */
    private function getWeekdayDate(int &$dayCount): string
    {
        do {
            $carbon = Carbon::now()->subDays($dayCount);

            $isWeekEnd = $carbon->isWeekend();
            if ($isWeekEnd) {
                $dayCount++;
            }
        } while($isWeekEnd);

        return $carbon->format('Ymd');
    }

    /**
     * @throws WrongDateException
     */
    public function getExchangeRates(string $date): void
    {
        // No data before that
        if ($date < self::DATA_FALLOFF_DATE) {
            $this->logManager->error('Wrong date');
            throw new WrongDateException();
        }

        try {
            $result = $this->factory->get($this->exchangeRateUrl."?date=".$date)->body();
        } catch (Throwable $exception) {
            $this->logManager->error($exception->getMessage());
            return;
        }

        $data = $this->xmlReaderWrapper->fromString($result)->value('CRates')->sole();
        $date = $data['Date'];
        $insert = [];

        foreach ($data['Currencies']['Currency'] as $currency) {
            $insert[] = [
                'date' => $date,
                'currency' => $currency['ID'],
                'rate' => $currency['Rate'],
            ];
        }

        $this->exchangeRateRepository->insert($insert);
    }
}

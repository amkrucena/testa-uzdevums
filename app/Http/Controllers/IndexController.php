<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ExchangeRates\services\ExchangeRateRetrievalService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    private const DATE_RANGE_MONTH_LIMIT = 12;
    private const DEFAULT_DATA_RANGE_IN_DAYS = 12;
    private const DEFAULT_DATE_FORMAT = 'Y-m-d';

    public function index(
        Request                      $request,
        ExchangeRateRetrievalService $exchangeRateRetrievalService
    ): RedirectResponse|Redirector|Response
    {
        $currencies = $exchangeRateRetrievalService->getExchangeRateCurrencies();
        $availableDates = $exchangeRateRetrievalService->getExchangeRateDates();

        try {
            $validatedData = $request->validate([
                'currencies' => 'nullable|string',
                'startDate' => 'date_format:Y-m-d|nullable|date',
                'endDate' => 'date_format:Y-m-d|nullable|date',
            ]);

            $currencyArray = $validatedData['currencies'] ?? "";

            if ($currencyArray) {
                $currencyArray = explode(',', $currencyArray);
                foreach ($currencyArray as $currency) {
                    if ($currency && !in_array($currency, $currencies)) {
                        throw ValidationException::withMessages(["Wrong currency given - $currency"]);
                    }
                }
            }

            if (array_key_exists('startDate', $validatedData)
                && Carbon::parse($validatedData['startDate'])->isBefore(
                    Carbon::parse($validatedData['endDate'])->subMonths(self::DATE_RANGE_MONTH_LIMIT))
            ) {
                throw ValidationException::withMessages(["Date range cannot be larger than 12 months!"]);
            }

        } catch (ValidationException $exception) {
            // Legit request cannot come in without the orderId parameter
            $default = $exchangeRateRetrievalService->getExchangeRates(
                Carbon::now()->subDays(self::DEFAULT_DATA_RANGE_IN_DAYS)->format(self::DEFAULT_DATE_FORMAT),
                date(self::DEFAULT_DATE_FORMAT),
                []
            );

            return Inertia::render('Index', [
                'dates' => $default['dates'],
                'rates' => $default['rates'],
                'currencies' => $currencies,
                'availableDates' => $availableDates,
                'errors' => Arr::flatten($exception->errors()),
                'defaultDateRange' => self::DEFAULT_DATA_RANGE_IN_DAYS
            ]);
        }

        $data = $exchangeRateRetrievalService->getExchangeRates(
            $validatedData['startDate'] ?? Carbon::now()->subDays(self::DEFAULT_DATA_RANGE_IN_DAYS)
                                                                ->format(self::DEFAULT_DATE_FORMAT),
            $validatedData['endDate'] ?? date(self::DEFAULT_DATE_FORMAT),
            $currencyArray ?: []
        );

        return Inertia::render('Index', [
            'dates' => $data['dates'],
            'rates' => $data['rates'],
            'currencies' => $currencies,
            'availableDates' => $availableDates,
            'defaultDateRange' => self::DEFAULT_DATA_RANGE_IN_DAYS
        ]);
    }
}

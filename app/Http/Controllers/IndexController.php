<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ExchangeRates\services\ExchangeRateRetrievalService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
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
                        throw ValidationException::withMessages(["Wrong currency given $currency"]);
                    }
                }
            }
        } catch (ValidationException $exception) {
            // Legit request cannot come in without the orderId parameter
            $default = $exchangeRateRetrievalService->getExchangeRates(
                date('Y-m-d'),
                date('Y-m-d'),
                []
            );

            return Inertia::render('Index', [
                'dates' => $default['dates'],
                'rates' => $default['rates'],
                'currencies' => $currencies,
                'availableDates' => $availableDates,
            ]);
        }

        $data = $exchangeRateRetrievalService->getExchangeRates(
            $validatedData['startDate'] ?? Carbon::now()->subDays(12)->format('Y-m-d'),
            $validatedData['endDate'] ?? date('Y-m-d'),
            $currencyArray ?: []
        );

        return Inertia::render('Index', [
            'dates' => $data['dates'],
            'rates' => $data['rates'],
            'currencies' => $currencies,
            'availableDates' => $availableDates,
        ]);
    }
}

<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\ExchangeRates\exceptions\WrongDateException;
use App\ExchangeRates\repositories\ExchangeRateRepository;
use App\ExchangeRates\services\ExchangeRateService;
use App\Wrappers\XmlReaderWrapper;
use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response;
use Illuminate\Log\LogManager;
use Mockery;
use PHPUnit\Framework\TestCase;
use Saloon\XmlWrangler\LazyQuery;
use Saloon\XmlWrangler\XmlReader;

class ExchangeRateServiceTest extends TestCase
{
    private Factory $factory;
    private XmlReaderWrapper $xmlReaderWrapper;
    private ExchangeRateRepository $exchangeRateRepository;
    private LogManager $logManager;
    private Repository $config;

    public function setUp(): void
    {
        $this->factory = Mockery::mock(Factory::class);
        $this->xmlReaderWrapper = Mockery::mock(XmlReaderWrapper::class);
        $this->exchangeRateRepository = Mockery::mock(ExchangeRateRepository::class);
        $this->logManager = Mockery::mock(LogManager::class);

        $this->config = Mockery::mock(Repository::class);
        $this->config->shouldReceive('get')
            ->with('bank.exchange_rate_url')
            ->andReturn('www.bank.lv/xml')
            ->once();
    }

    public function test_getExchangeRates_WithCurrentDate_NoErrors(): void
    {
        $this->mockCorrectData();

        $exchangeRateService = new ExchangeRateService(
            $this->factory,
            $this->xmlReaderWrapper,
            $this->exchangeRateRepository,
            $this->logManager,
            $this->config
        );

        $exchangeRateService->getExchangeRates('20240903');
        $this->expectNotToPerformAssertions();
    }

    public function test_getExchangeRates_WithCurrentDate_ThrowsDateError(): void
    {
        $this->logManager->shouldReceive('error')->once();

        $exchangeRateService = new ExchangeRateService(
            $this->factory,
            $this->xmlReaderWrapper,
            $this->exchangeRateRepository,
            $this->logManager,
            $this->config
        );
        $this->expectException(WrongDateException::class);
        $exchangeRateService->getExchangeRates('19980903');
    }

    public function test_getExchangeRates_WithCurrentDate_ThrowsUrlError(): void
    {
        $this->logManager->shouldReceive('error')->once();

        $exchangeRateService = new ExchangeRateService(
            $this->factory,
            $this->xmlReaderWrapper,
            $this->exchangeRateRepository,
            $this->logManager,
            $this->config
        );

        $exchangeRateService->getExchangeRates('20240903');
        $this->expectNotToPerformAssertions();
    }

    public function test_getExchangeRatesForDays_NoErrors(): void
    {
        $this->mockCorrectData(2);

        $exchangeRateService = new ExchangeRateService(
            $this->factory,
            $this->xmlReaderWrapper,
            $this->exchangeRateRepository,
            $this->logManager,
            $this->config
        );

        $result = $exchangeRateService->getExchangeRatesForDays(2);

        $dayCount = 0;
        $date = $this->getWeekdayDate($dayCount);
        $dayCount++;
        $expectedResult = [
            $date,
            $this->getWeekdayDate($dayCount)
        ];

        $this->assertEquals($expectedResult, $result);
    }

    private function getWeekdayDate(int &$dayCount)
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

    private function mockCorrectData(int $times = 1): void
    {
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('body')
            ->andReturn(
                "<breakfast_menu>
                <script/>
                <food>
                <name>Belgian Waffles</name>
                <price>$5.95</price>
                <description>Two of our famous Belgian Waffles with plenty of real maple syrup</description>
                <calories>650</calories>
                </food>
                </breakfast_menu>"
            )
            ->times($times);
        $this->factory->shouldReceive('get')->andReturn($response)->times($times);

        $lazyQuery = Mockery::mock(LazyQuery::class);
        $lazyQuery->shouldReceive('sole')
            ->andReturn(
                [
                    'Date' => 20240903,
                    'Currencies' => [
                        'Currency' => [
                            [
                                'ID' => 'EUR',
                                'Rate' => 1.09234
                            ],
                        ]
                    ]
                ]
            )
            ->times($times);
        $xmlReader = Mockery::mock(XmlReader::class);
        $xmlReader->shouldReceive('value')->andReturn($lazyQuery)->times($times);
        $this->xmlReaderWrapper->shouldReceive('fromString')->andReturn($xmlReader)->times($times);

        $this->exchangeRateRepository->shouldReceive('insert')->times($times);
    }
}

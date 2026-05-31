<?php

namespace Tests\Unit;

use App\Services\QuotationService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuotationServiceTest extends TestCase
{
    use RefreshDatabase;
    
    private QuotationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QuotationService();
    }

    /** @test */
    public function it_calculates_worked_example_correctly(): void
    {
        // From the spec: ages 28,35 | 30 days | expected total 117.00
        $quotation = $this->service->calculate([
            'age'         => '28,35',
            'currency_id' => 'EUR',
            'start_date'  => '2020-10-01',
            'end_date'    => '2020-10-30',
        ]);

        $this->assertEquals(117.00, $quotation->total);
        $this->assertEquals(30, $quotation->trip_days);
    }

    /** @test */
    public function it_throws_for_out_of_range_age(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->service->calculate([
            'age'         => '75',
            'currency_id' => 'EUR',
            'start_date'  => '2024-01-01',
            'end_date'    => '2024-01-10',
        ]);
    }
}
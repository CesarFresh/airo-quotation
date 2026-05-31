<?php
namespace App\Services;

use App\Models\Quotation;
use Carbon\Carbon;
use InvalidArgumentException;

class QuotationService
{
    private const FIXED_RATE = 3.0;

    private const AGE_LOADS = [
        [18, 30, 0.6],
        [31, 40, 0.7],
        [41, 50, 0.8],
        [51, 60, 0.9],
        [61, 70, 1.0],
    ];

    /**
    * Calcula y persiste una cotización.
    * @throws InvalidArgumentException si alguna edad está fuera del rango.
    */
    public function calculate(array $data): Quotation
    {
        $ages  = $this->parseAges($data['age']);
        $start = Carbon::parse($data['start_date']);
        $end   = Carbon::parse($data['end_date']);
        $days  = $start->diffInDays($end) + 1;

        $total = 0.0;
        foreach ($ages as $age) {
            $load = $this->getAgeLoad($age);
            $total += self::FIXED_RATE * $load * $days;
            error_log("Age: {$age}, Load: {$load}, Days: {$days}, Subtotal: " . (self::FIXED_RATE * $load * $days));
        }

        return Quotation::create([
            'ages'        => $data['age'],
            'currency_id' => $data['currency_id'],
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
            'trip_days'   => $days,
            'total'       => round($total, 2),
        ]);
    }

    private function parseAges(string $agesCsv): array
    {
        return array_map(
            fn (string $age): int => (int) trim($age),
            explode(',', $agesCsv)
        );
    }

    private function getAgeLoad(int $age): float
    {
        foreach (self::AGE_LOADS as [$min, $max, $load]) {
            if ($age >= $min && $age <= $max) {
                return $load;
            }
        }
        throw new InvalidArgumentException(
            "Age {$age} is outside the supported range (18-70)."
        );
    }
}
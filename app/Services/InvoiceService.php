<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class InvoiceService
{
    private static array $romanMonths = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
        5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
        9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    public function generateInvoiceNumber(?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();
        $monthRoman = self::$romanMonths[$date->month] ?? 'I';
        $year = $date->year;

        // Count existing orders for the same month and year to determine sequence
        $prefix = "INV/{$monthRoman}/{$year}/";

        // Query max order count for this month/year safely inside database transaction if needed
        $count = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $date->month)
            ->count();

        $sequence = str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);

        $invoiceNumber = $prefix.$sequence;

        // Ensure uniqueness in case of race conditions
        $existing = Order::where('invoice_number', $invoiceNumber)->exists();
        if ($existing) {
            $maxId = Order::whereYear('created_at', $year)
                ->whereMonth('created_at', $date->month)
                ->max('id');
            $sequence = str_pad((string) ($maxId + 1), 3, '0', STR_PAD_LEFT);
            $invoiceNumber = $prefix.$sequence;
        }

        return $invoiceNumber;
    }
}

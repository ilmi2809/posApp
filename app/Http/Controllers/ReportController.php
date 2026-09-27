<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        $orderDetails = OrderDetail::with(['order', 'item.uom'])
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('transaction_date', [$startDate, $endDate]);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('reports.sales', compact('orderDetails', 'startDate', 'endDate'));
    }

    public function stock(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('date'))
            : Carbon::today();

        $stocks = Stock::with(['item.uom', 'item.price'])
            ->orderBy('quantity', 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.stock', compact('stocks', 'date'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\Stock;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('Kasir')) {
            return redirect()->route('orders.create');
        }

        if ($user->hasRole('Admin Stock')) {
            return redirect()->route('stocks.index');
        }

        // Superadmin Executive Dashboard
        $today = Carbon::today();
        
        $totalSalesToday = Order::whereDate('transaction_date', $today)->sum('total');
        $totalOrdersToday = Order::whereDate('transaction_date', $today)->count();
        $totalItems = Item::count();
        
        $lowStockItems = Stock::with(['item.uom'])
            ->where('quantity', '<=', 10)
            ->get();
            
        $recentOrders = Order::with(['user', 'paymentMethod'])
            ->latest()
            ->take(5)
            ->get();

        $recentMovements = StockMovement::with(['item', 'user'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalSalesToday',
            'totalOrdersToday',
            'totalItems',
            'lowStockItems',
            'recentOrders',
            'recentMovements'
        ));
    }
}

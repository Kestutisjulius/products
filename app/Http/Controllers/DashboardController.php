<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\User;
use App\Models\Stock;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products'     => Product::count(),
            'users'        => User::count(),
            'stockEntries' => Stock::count(),
            'totalStock'   => (int) Stock::sum('stock'),
        ];


        return Inertia::render('Dashboard', [
            'stats' => $stats
        ]);
    }
}

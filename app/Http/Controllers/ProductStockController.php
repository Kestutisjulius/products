<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductStockController extends Controller
{
    //api:Stock
    public function show(Product $product, Request $request)
    {
        abort_unless($request->user()->tokenCan('stocks:read'), 403);

        return response()->json([
            'sku'    => $product->sku,
            'stocks' => $product->stocks()->get(['city','stock']),
        ]);
    }
}

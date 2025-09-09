<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use App\Models\Product;

class UpdateStock extends Command
{

    protected $signature = 'product:update-stock';
    protected $description = 'Update Stock from a remote JSON URL.';


    public function handle()
    {
        $url ='https://kinfirm.com/app/uploads/laravel-task/stocks.json';

         try {
            $this->info("Fetching stock data from: {$url}...");
            $response = Http::get($url);

            if ($response->failed()) {
                $this->error('Failed to retrieve stock data from URL.');
                return 1;
            }

            $stocks = $response->json();

            if (empty($stocks)) {
                $this->info('No stock data found or JSON is empty.');
                return 0;
            }

            $bar = $this->output->createProgressBar(count($stocks));
            $bar->start();

            foreach ($stocks as $stockData) {
                $product = Product::where('sku', $stockData['sku'])->first();

                if ($product) {
                    $product->stocks()->updateOrCreate(
                    ['city' =>$stockData['city']], 
                    ['stock' => $stockData['stock']]
                );
                } else {
                    Log::warning("Product with SKU '{$stockData['sku']}' not found.");
                }
                $bar->advance();
            }

            $bar->finish();
            $this->info("\nStock import complete. Processed " . count($stocks) . " items.");
            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to import stock: ' . $e->getMessage());
            Log::error('Stock import failed: ' . $e->getMessage());
            return 1;
        }

    }
}

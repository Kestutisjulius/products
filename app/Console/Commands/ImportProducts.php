<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\ImportProductJob;

class ImportProducts extends Command
{
    protected $signature = 'products:import {--url=https://kinfirm.com/app/uploads/laravel-task/products.json : URL to fetch products JSON from}';
    protected $description = 'Import products from a given JSON URL and dispatch to queue.';

    public function handle()
    {
        
       $url = $this->option('url');
       if (!$url) {
            $this->error('The --url option is required.');
            return 1;
        }

        try {
            $this->info("Fetching products from: {$url}...");
            
            // Naudojame Http fasadą
            $response = Http::get($url);

            if ($response->failed()) {
                $this->error('Failed to retrieve data from URL.');
                return 1;
            }

            $products = $response->json();

            if (empty($products)) {
                $this->info('No products found or JSON is empty.');
                return 0;
            }



            $this->info('Products found. Dispatching jobs to the queue...');
            $bar = $this->output->createProgressBar(count($products));
            $bar->start();

            foreach ($products as $productData) {

                ImportProductJob::dispatch($productData);
                $bar->advance();
            }

            $bar->finish();
            $this->info("\nAll product import jobs have been dispatched to the queue.");
            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to import products: ' . $e->getMessage());
            Log::error('Product import failed: ' . $e->getMessage());
            return 1;
        }

    }
}

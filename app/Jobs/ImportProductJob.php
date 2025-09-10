<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use App\Models\Tag;
use Illuminate\Support\Str;

class ImportProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $productData;

    public function __construct(array $productData)
    {
        $this->productData = $productData;

    }


    public function handle(): void
    {
        
        try {
        DB::beginTransaction();

        $product = 
            Product::updateOrCreate(
            ['sku' => $this->productData['sku']],
            [
                'description' => $this->productData['description'] ?? null,
                'size'        => $this->productData['size'] ?? null,
                'photo'       => $this->productData['photo'] ?? null,
                'updated_at'  => $this->productData['updated_at'] ?? now(),
            ]
        );
        

        if (!empty($this->productData['tags'])) {
            foreach ($this->productData['tags'] as $tagData) {
                $product->tags()->updateOrCreate(
                    ['title' => $tagData['title']]
                );
            }
        }

        DB::commit();
        Cache::forget("product_info_{$product->sku}");

    } catch (\Exception $e) {
        DB::rollBack();
        report($e);
    }
    }
}

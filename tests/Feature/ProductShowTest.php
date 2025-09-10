<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

use PHPUnit\Framework\Attributes\Test;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_product_show_page(): void
    {
        $product = Product::factory()->create(['sku' => 'SKU123']);
        $tag = Tag::factory()->create(['product_id' => $product->id, 'title' => 'Ekologiškas']);
        $stock = Stock::factory()->create(['product_id' => $product->id, 'city' => 'Vilnius', 'stock' => 10]);

        $response = $this->get(route('products.show', $product->sku));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Products/Show')
                ->has('product', fn ($productData) =>
                    $productData
                        ->where('sku', $product->sku)
                        ->where('description', $product->description)
                        ->where('photo', $product->photo)
                        ->has('tags', 1)
                        ->has('stocks', 1)
                        ->etc()
                )
        );
    }

    #[Test]
    public function it_uses_cache_for_product_info(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(function ($key, $ttl, $callback) {
                return str_contains($key, 'product_info_');
            })
            ->andReturn([
                'sku' => 'TEST',
                'description' => 'Test desc',
                'photo' => '/images/test.jpg',
                'tags' => [],
            ]);

        $product = Product::factory()->create(['sku' => 'TEST']);
        $this->get(route('products.show', $product->sku))->assertStatus(200);
    }
}

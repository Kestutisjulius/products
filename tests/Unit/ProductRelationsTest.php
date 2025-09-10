<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Tag;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;

use PHPUnit\Framework\Attributes\Test;

class ProductRelationsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function product_has_many_tags(): void
    {
        $product = Product::factory()->create();
        $tag = Tag::factory()->create(['product_id' => $product->id]);

        $this->assertTrue($product->tags->contains($tag));
    }

    #[Test]
    public function product_has_many_stocks(): void
    {
        $product = Product::factory()->create();
        $stock = Stock::factory()->create(['product_id' => $product->id]);

        $this->assertTrue($product->stocks->contains($stock));
    }
}

<?php

namespace Tests\Unit;

use App\Clients\StockStatus;
use App\Models\History;
use App\Models\Product;
use App\Models\Stock;
use Database\Seeders\RetailerWithProductSeeder;
use Facades\App\Clients\ClientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function testItRecordsHistoryEachTimeStockIsTracked()
    {
        $this->seed(RetailerWithProductSeeder::class);

        $this->mockClientRequest($available = true, $price = 9900);

        $product = tap(Product::first(), function ($product) {
            $product = Product::first();
            $this->assertCount(0, $product->history);

            $product->track();

            $this->assertCount(1, $product->refresh()->history);
        });


        $history = $product->history->first();

        $this->assertEquals($price, $history->price);
        $this->assertEquals($available, $history->in_stock);
        $this->assertEquals($product->id, $history->product_id);
        $this->assertEquals($product->stock[0]->id, $history->stock_id);
    }
}

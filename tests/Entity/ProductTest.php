<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testcomputeTVAFoodProduct()
    {
        $product = new Product('Un produit', Product::FOOD_PRODUCT, 20);
        $this->assertSame(1.1, $product->computeTVA());
    }

    public function testcomputeTVAOtherProduct()
    {
        $product = new Product('Un autre produit', 'Fruit', 20);
        $this->assertSame(3.92, $product->computeTVA());
    }
}

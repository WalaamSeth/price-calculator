<?php

namespace App\Tests\Unit;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Service\PriceCalculator;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $calculator;
    private $productRepository;
    private $couponRepository;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->couponRepository = $this->createMock(CouponRepository::class);
        
        $this->calculator = new PriceCalculator(
            $this->productRepository,
            $this->couponRepository
        );
    }

    public function testCalculatePriceWithoutCoupon(): void
    {
        $product = new Product();
        $product->setPrice(100.00);
        
        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);
        
        $price = $this->calculator->calculate(1, 'DE123456789', null);
        
        $this->assertEquals(119.00, $price);
    }

    public function testCalculatePriceWithFixedCoupon(): void
    {
        $product = new Product();
        $product->setPrice(100.00);
        
        $coupon = new Coupon();
        $coupon->setType('fixed');
        $coupon->setValue(15.00);
        
        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);
        
        $this->couponRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'D15'])
            ->willReturn($coupon);
        
        $price = $this->calculator->calculate(1, 'DE123456789', 'D15');
        
        $this->assertEquals(101.15, $price);
    }

    public function testCalculatePriceWithPercentCoupon(): void
    {
        $product = new Product();
        $product->setPrice(100.00);
        
        $coupon = new Coupon();
        $coupon->setType('percent');
        $coupon->setValue(10);
        
        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);
        
        $this->couponRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'P10'])
            ->willReturn($coupon);
        
        $price = $this->calculator->calculate(1, 'IT12345678900', 'P10');
        
        $this->assertEquals(109.8, $price);
    }

    public function testCalculatePriceWithDifferentTaxRates(): void
    {
        $product = new Product();
        $product->setPrice(100.00);
        
        $this->productRepository
            ->expects($this->exactly(4))
            ->method('find')
            ->willReturn($product);
        
        $prices = [
            'DE' => $this->calculator->calculate(1, 'DE123456789', null),
            'IT' => $this->calculator->calculate(1, 'IT12345678900', null),
            'FR' => $this->calculator->calculate(1, 'FRAB123456789', null),
            'GR' => $this->calculator->calculate(1, 'GR123456789', null),
        ];
        
        $this->assertEquals(119.00, $prices['DE']);
        $this->assertEquals(122.00, $prices['IT']);
        $this->assertEquals(120.00, $prices['FR']);
        $this->assertEquals(124.00, $prices['GR']);
    }

    public function testCalculatePriceWithProductNotFound(): void
    {
        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product not found');
        
        $this->calculator->calculate(999, 'DE123456789', null);
    }

    public function testApplyCouponToZeroPrice(): void
    {
        $product = new Product();
        $product->setPrice(10.00);
        
        $coupon = new Coupon();
        $coupon->setType('fixed');
        $coupon->setValue(15.00);
        
        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);
        
        $this->couponRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'D15'])
            ->willReturn($coupon);
        
        $price = $this->calculator->calculate(1, 'DE123456789', 'D15');
        
        $this->assertEquals(0, $price);
    }
}

<?php

namespace App\Service;

use App\Entity\Coupon;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;

class PriceCalculator
{
    private const TAX_RATES = [
        'DE' => 0.19,
        'IT' => 0.22,
        'FR' => 0.20,
        'GR' => 0.24,
    ];

    public function __construct(
        private ProductRepository $productRepository,
        private CouponRepository $couponRepository
    ) {}

    public function calculate(int $productId, string $taxNumber, ?string $couponCode = null): float
    {
        $product = $this->productRepository->find($productId);
        if (!$product) {
            throw new \InvalidArgumentException('Product not found');
        }

        $price = $product->getPrice();

        if ($couponCode) {
            $coupon = $this->couponRepository->findOneBy(['code' => $couponCode]);
            if ($coupon) {
                $price = $this->applyCoupon($price, $coupon);
            }
        }

        $countryCode = substr($taxNumber, 0, 2);
        $taxRate = self::TAX_RATES[$countryCode] ?? 0;
        $price = $price * (1 + $taxRate);

        return round($price, 2);
    }

    private function applyCoupon(float $price, Coupon $coupon): float
    {
        if ($coupon->getType() === 'fixed') {
            return max(0, $price - $coupon->getValue());
        }
        
        return $price * (1 - $coupon->getValue() / 100);
    }
}

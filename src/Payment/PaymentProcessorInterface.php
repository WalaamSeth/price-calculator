<?php

namespace App\Payment;

interface PaymentProcessorInterface
{
    public function process(float $amount): bool;
}

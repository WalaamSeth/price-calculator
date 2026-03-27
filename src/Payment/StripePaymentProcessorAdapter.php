<?php

namespace App\Payment;

use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class StripePaymentProcessorAdapter implements PaymentProcessorInterface
{
    private StripePaymentProcessor $processor;

    public function __construct()
    {
        $this->processor = new StripePaymentProcessor();
    }

    public function process(float $amount): bool
    {
        return $this->processor->processPayment($amount);
    }
}

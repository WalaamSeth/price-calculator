<?php

namespace App\Payment;

use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

class PaypalPaymentProcessorAdapter implements PaymentProcessorInterface
{
    private PaypalPaymentProcessor $processor;

    public function __construct()
    {
        $this->processor = new PaypalPaymentProcessor();
    }

    public function process(float $amount): bool
    {
        try {
            $this->processor->pay($amount);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

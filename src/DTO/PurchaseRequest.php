<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PurchaseRequest extends CalculatePriceRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['paypal', 'stripe'])]
    public string $paymentProcessor;
}

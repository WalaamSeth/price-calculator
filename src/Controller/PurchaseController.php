<?php

namespace App\Controller;

use App\DTO\PurchaseRequest;
use App\Payment\PaymentProcessorInterface;
use App\Payment\PaypalPaymentProcessorAdapter;
use App\Payment\StripePaymentProcessorAdapter;
use App\Service\PriceCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PurchaseController extends AbstractController
{
    public function __construct(
        private PriceCalculator $priceCalculator,
        private ValidatorInterface $validator,
        private iterable $paymentProcessors
    ) {}

    #[Route('/purchase', name: 'purchase', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->product = $data['product'] ?? null;
        $purchaseRequest->taxNumber = $data['taxNumber'] ?? null;
        $purchaseRequest->couponCode = $data['couponCode'] ?? null;
        $purchaseRequest->paymentProcessor = $data['paymentProcessor'] ?? null;
        
        $errors = $this->validator->validate($purchaseRequest);
        
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $price = $this->priceCalculator->calculate(
                $purchaseRequest->product,
                $purchaseRequest->taxNumber,
                $purchaseRequest->couponCode
            );
            
            $processor = $this->getPaymentProcessor($purchaseRequest->paymentProcessor);
            
            if (!$processor || !$processor->process($price)) {
                return $this->json(['error' => 'Payment processing failed'], Response::HTTP_BAD_REQUEST);
            }
            
            return $this->json(['status' => 'success'], Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    
    private function getPaymentProcessor(string $type): ?PaymentProcessorInterface
    {
        $processorMap = [
            'paypal' => PaypalPaymentProcessorAdapter::class,
            'stripe' => StripePaymentProcessorAdapter::class,
        ];
        
        $expectedClass = $processorMap[$type] ?? null;
        
        if (!$expectedClass) {
            return null;
        }
        
        foreach ($this->paymentProcessors as $processor) {
            if ($processor instanceof $expectedClass) {
                return $processor;
            }
        }
        
        return null;
    }
}

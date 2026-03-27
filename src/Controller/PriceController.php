<?php

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\Service\PriceCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceController extends AbstractController
{
    public function __construct(
        private PriceCalculator $priceCalculator,
        private ValidatorInterface $validator
    ) {}

    #[Route('/calculate-price', name: 'calculate_price', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $calculatePriceRequest = new CalculatePriceRequest();
        $calculatePriceRequest->product = $data['product'] ?? null;
        $calculatePriceRequest->taxNumber = $data['taxNumber'] ?? null;
        $calculatePriceRequest->couponCode = $data['couponCode'] ?? null;
        
        $errors = $this->validator->validate($calculatePriceRequest);
        
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $price = $this->priceCalculator->calculate(
                $calculatePriceRequest->product,
                $calculatePriceRequest->taxNumber,
                $calculatePriceRequest->couponCode
            );
            
            return $this->json(['price' => $price], Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}

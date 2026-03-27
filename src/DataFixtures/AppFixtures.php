<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Coupon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $iphone = new Product();
        $iphone->setName('Iphone');
        $iphone->setPrice(100.00);
        $manager->persist($iphone);
        
        $headphones = new Product();
        $headphones->setName('Наушники');
        $headphones->setPrice(20.00);
        $manager->persist($headphones);
        
        $case = new Product();
        $case->setName('Чехол');
        $case->setPrice(10.00);
        $manager->persist($case);

        $coupon1 = new Coupon();
        $coupon1->setCode('D15');
        $coupon1->setType('fixed');
        $coupon1->setValue(15.00);
        $manager->persist($coupon1);
        
        $coupon2 = new Coupon();
        $coupon2->setCode('P10');
        $coupon2->setType('percent');
        $coupon2->setValue(10);
        $manager->persist($coupon2);
        
        $coupon3 = new Coupon();
        $coupon3->setCode('P100');
        $coupon3->setType('percent');
        $coupon3->setValue(100);
        $manager->persist($coupon3);
        
        $manager->flush();
        
        echo "Loaded " . count($manager->getRepository(Product::class)->findAll()) . " products\n";
        echo "Loaded " . count($manager->getRepository(Coupon::class)->findAll()) . " coupons\n";
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('us_US');

        $phones = [
            "iPhone X",
            "iPhone 11",
            "iPhone 12 Mini",
            "iPhone 13",
            "iPhone 13 Mini",
            "iPhone 13 Pro",
            "iPhone 13 Pro Max",
            "Samsung Galaxy S20",
            "Samsung Galaxy S20 Plus",
            "Samsung Galaxy S20 Ultra",
            "Samsung Galaxy S21",
            "Samsung Galaxy S21 Plus",
            "Samsung Galaxy S21 Ultra",
            "Samsung Galaxy S22",
            "Samsung Galaxy S22 Plus",
            "Samsung Galaxy S22 Ultra"
        ];

        foreach ($phones as $phone) {
            $product = new Product();

            $product->setName($phone)
                ->setDescription($faker->paragraphs(mt_rand(2, 4), true))
                ->setQuantity($faker->numberBetween(1000, 2000))
                ->setPrice($faker->numberBetween(799, 1199))
                ->setPublishedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months', 'now')));

            $manager->persist($product);
        }
        $manager->flush();
    }
}

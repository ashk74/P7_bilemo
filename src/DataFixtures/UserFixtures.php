<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\CustomerRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class UserFixtures extends Fixture
{
    private CustomerRepository $customerRepo;

    public function __construct(CustomerRepository $customerRepo)
    {
        $this->customerRepo = $customerRepo;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('us_US');
        $customers = $this->customerRepo->findAll();

        foreach ($customers as $customer) {
            for ($i = 0; $i < 5; $i++) {
                $user = new User();
                $user->setFirstname($faker->firstName())
                    ->setLastname($faker->lastname())
                    ->setEmail($faker->freeEmail())
                    ->setCustomer($customer)
                    ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months', 'now')));

                $manager->persist($user);
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CustomerFixtures::class,
        ];
    }
}

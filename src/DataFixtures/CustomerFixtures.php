<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CustomerFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('us_US');

        $customer = new Customer();
        $customer->setEmail('customer01@email.com')
            ->setName('Stamm Ltd')
            ->setPassword($this->passwordHasher->hashPassword($customer, '123456789'))
            ->setSiret('12356894100055')
            ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months', 'now')));

        $manager->persist($customer);

        for ($i = 0; $i < 10; $i++) {
            $customer = new Customer();
            $customer->setEmail($faker->freeEmail())
                ->setName($faker->company())
                ->setPassword($this->passwordHasher->hashPassword($customer, 'password'))
                ->setSiret($faker->numberBetween(12356894100056, 12356894100999))
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months', 'now')));

            $manager->persist($customer);
        }

        $manager->flush();
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture {

  /**
   * L'encodeur de mots de passe
   *
   * @var UserPasswordEncoderInterface
   */
  private $encoder;

  public function __construct(UserPasswordEncoderInterface $encoder) {
    $this->encoder = $encoder;
  }

  public function load(ObjectManager $manager) {
    $faker = Factory::create("fr_FR");

    // Créer 10 utilisateurs
    for ($u = 0 ; $u < 10 ; $u++) {
      $user = new User();

      $chrono = 1;
      // Via le fichier config/packages/security.yaml, sur le champ encoder, la fonction va connaître les spécificités de l'encodeur (sha256, sha512, etc)
      $hash = $this->encoder->encodePassword($user, "password");

      $user->setFirstname($faker->firstName())
           ->setLastname($faker->lastName)
           ->setEmail($faker->email)
           ->setPassword($hash);

      $manager->persist($user);

      // Créer entre 5 et 20 clients
      for ($i = 0 ; $i < mt_rand(5, 20) ; $i++) {
        $customer = new Customer();
        $customer->setFirstname($faker->firstName())
                 ->setLastname($faker->lastName)
                 ->setEmail($faker->email)
                 ->setCompany($faker->company)
                 ->setUser($user);

        $manager->persist($customer);

        // Créer entre 1 et 10 factures
        for ($j = 0 ; $j < mt_rand(1, 10) ; $j++) {
          $invoice = new Invoice();
          $invoice->setAmount($faker->randomFloat(2, 250, 5000))
                  ->setSentAt($faker->dateTimeBetween("-6 months"))
                  ->setStatus($faker->randomElement(["SENT", "PAID", "CANCELLED"]))
                  ->setCustomer($customer)
                  ->setChrono($chrono++);

          $manager->persist($invoice);
        }
      }
    }

    $manager->flush();
  }
}

<?php

namespace App\DataFixtures;

use App\Entity\Reponse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReponseFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");
        $questionsUtilise = [];

        for ($i=1; $i<=10; $i++) {
            $questionRef= $faker->numberBetween(1,10);
            while (in_array($questionRef, $questionsUtilise)) {
                $questionRef= $faker->numberBetween(1,10);
            }
            $questionsUtilise[] = $questionRef;

            //Reponse correcte
            $reponse = new Reponse();
            $reponse->setLibelle($faker->words(4, true));
            $reponse->setEstCorrecte(true);
            $reponse->setQuestion($this->getReference("question_".$questionRef));
            $manager->persist($reponse);

            for ($j=1; $j<=3; $j++) {
                $reponse = new Reponse();
                $reponse->setLibelle($faker->words(8, true));
                $reponse->setEstCorrecte(false);
                $reponse->setQuestion($this->getReference("question_".$questionRef));
                $manager->persist($reponse);
            }

        }

        $manager->flush();
    }

    public function getDependencies()
    {
        // Retourne un tableau de fixtures
        return [
            QuestionFixtures::class
        ];
    }
}

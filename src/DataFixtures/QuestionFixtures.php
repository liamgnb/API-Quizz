<?php

namespace App\DataFixtures;

use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class QuestionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");

        for ($i=1; $i<=10; $i++) {
            $question = new Question();
            $question->setLibelle($faker->paragraph());

            // Récupérer un thème
            $themeRef= $faker->numberBetween(1,10);
            $question->setTheme($this->getReference("theme_".$themeRef));

            // Créer une référence
            $this->addReference("question_".$i,$question);
            $manager->persist($question);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        // Retourne un tableau de fixtures
        return [
            ThemeFixtures::class
        ];
    }
}

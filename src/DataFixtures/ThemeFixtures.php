<?php

namespace App\DataFixtures;

use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ThemeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $themes = [
            1 => "Nature",
        ];

        foreach ($themes as $th)
        {
            $theme = new Theme();
            $theme->setLibelle($th);

            $manager->persist($theme);
        }

        $manager->flush();
    }
}

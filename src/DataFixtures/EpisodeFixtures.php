<?php

namespace App\DataFixtures;

use App\Service\Slugify;
use Faker;
use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var Slugify
     */
    private $slugify;

    /**
     * EpisodeFixtures constructor.
     * @param Slugify $slugify
     */
    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 100; $i++) {
            $episode = new Episode();
            $episode->setTitle($faker->sentence(4,true));
            $episode->setSlug($this->slugify->generate($episode->getTitle()));
            $episode->setNumber($faker->randomDigitNotNull);
            $episode->setSynopsis($faker->paragraph(10, true));
            $episode->setSeason($this->getReference('season_' . rand(0,49)));
            $manager->persist($episode);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [SeasonFixtures::class];
    }
}
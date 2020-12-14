<?php

namespace App\DataFixtures;

use App\Service\Slugify;
use Faker;
use App\Entity\Actor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ActorFixtures extends Fixture implements DependentFixtureInterface
{
    const ACTORS = [
        'Andrew Lincoln',
        'Norman Reedus',
        'Lauren Cohan',
        'Danai Gurira',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::ACTORS as $key => $actorName) {
            $slug = new Slugify();
            $actor = new Actor();
            $actor->setName($actorName);
            $actor->addProgram($this->getReference('program_1' ));
            $slug = $slug->generate($actor->getName());
            $actor->setSlug($slug);
            $manager->persist($actor);
        }
        $faker = Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 50; $i++) {
            $actor = new Actor();
            $slug = new Slugify();
            $actor->setName($faker->name);
            $actor->addProgram($this->getReference('program_' . rand(0,5)));
            $slug = $slug->generate($actor->getName());
            $actor->setSlug($slug);
            $manager->persist($actor);
        }

            $manager->flush();
    }

    public function getDependencies()
    {
        return [ProgramFixtures::class];
    }
}
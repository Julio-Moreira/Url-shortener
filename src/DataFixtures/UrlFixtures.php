<?php

namespace App\DataFixtures;

use App\Entity\Url;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UrlFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $url = new Url('https://www.google.com', 'https://localhost', '');
        $url->wasAccessed();
        $url->wasAccessed(new \DateTimeImmutable('10 minutes ago'));
        $url->wasAccessed();

        $manager->persist($url);
        $manager->flush();
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Url;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UrlFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $url = new Url('https://www.google.com', 'https://localhost');
        $url->wasAccessedIn();
        $url->wasAccessedIn(new \DateTimeImmutable('10 minutes ago'));
        $url->wasAccessedIn();

        $url_2 = new Url('https://www.github.com', 'https://localhost');
        $url_2->wasAccessedIn();
        $url_2->wasAccessedIn();
        $url_2->wasAccessedIn(new \DateTimeImmutable('15 minutes ago'));

        $manager->persist($url);
        $manager->persist($url_2);
        $manager->flush();
    }
}

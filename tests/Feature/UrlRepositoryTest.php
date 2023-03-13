<?php

use App\Entity\Url;
use App\Repository\UrlRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

uses(KernelTestCase::class)->group('feature');

beforeEach(function() {
    $kernel = self::bootKernel();
    $entityManager = $kernel->getContainer()
        ->get('doctrine')
        ->getManager();
    
    /** @var UrlRepository $urlRepository */
    $this->urlRepository = $entityManager->getRepository(Url::class);
    $url = new Url('https://www.google.com', 'https://', 'a');
    $url_2 = new Url('https://www.google.com', 'https://', 'd');
    $this->urlRepository->save($url, false);
    $this->urlRepository->save($url_2);
});

test("test if url is correctly get in db", function() {
    $url = $this->urlRepository
        ->findAll()[0];
    $getUrl = $this->urlRepository
        ->get($url->id);
    
    expect($getUrl)->toBe($url);
});

test("test if url is correctly save in db", function() {
    $url = new Url('https://www.google.com', 'https://', 'a');
    $this->urlRepository
        ->save($url, false);

    $allUrls = $this->urlRepository->findAll();

    expect($allUrls)->sequence(
        fn($urlInDb) => $urlInDb->not->toBe($url),
        fn($urlInDb) => $urlInDb->not->toBe($url),
        fn($urlInDb) => $urlInDb->toBe($url)
    );
});

test("test if url is correctly deleted in the db", function() {
    $url = $this->urlRepository
        ->findAll()[0];
    
    $this->urlRepository->removeById($url->id);
    
    $url_2 = $this->urlRepository
        ->findAll()[0];
    
    expect($url)->not->toBe($url_2);
});
<?php

use App\Entity\Url;
use Symfony\Component\Uid\Ulid;

uses()->group('unit');

dataset('url', [
    [
        'url' => new Url("https://www.google.com", 'https://test'), 
        'url2' => new Url("https://www.google.com", 'https://test') ]   
]);

test('If url is correctly initializing generated', function(Url $url) {
    expect($url->completeUrl)->toBe("https://www.google.com");
    expect($url->shortUrl)->toMatch("/https:\/\/test\/0x.{32,64}/");
    expect($url->id)->toStartWith('0x');
    expect($url->createdAt)
        ->toBeInstanceOf(\DateTimeImmutable::class)
        ->not->toBe(new \DateTimeImmutable());
})->with('url');

test('If access is correctly stored', function(Url $url) {
    $urlDate = new \DateTimeImmutable('5 seconds ago');
    $url->wasAccessedIn($urlDate);
    $url->wasAccessedIn();
    $url->wasAccessedIn();
    $urlAccesses = $url->accesses();

    expect($urlAccesses->count())->toBe(3);
    expect($urlAccesses->first())->toMatch("/..-..-.. \/ ..:..:../");
    expect($urlAccesses)->sequence(
        fn($date) => $date->toBe($urlDate->format('y-m-d / G:i:s')),
        fn($date) => $date->not->toBe(
            (new \DateTimeImmutable())->format('y-m-d / G:i:s~u')
        ),
        fn($date) => $date->toMatch("/..-..-.. \/ ..:..:../"),
    );
})->with('url');

test('If two urls with same complete url generate diferent short url', function(Url $url_1, Url $url_2) {
    expect($url_1->shortUrl)->not->toBe($url_2->shortUrl);
    expect($url_1->createdAt)->not->toBe($url_2->createdAt);
})->with('url');
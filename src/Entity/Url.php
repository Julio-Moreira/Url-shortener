<?php
namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;

#[ORM\Entity(repositoryClass: UrlRepository::class)]
class Url
{
    #[ORM\Id, ORM\Column(length: 64, unique: true)]
    public readonly string $id;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    public readonly \DateTimeImmutable $createdAt;
    
    #[ORM\Column(length: 255)]
    public readonly string $shortUrl;
    
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $accesses = [];

    public function __construct(
        #[ORM\Column(length: 255)]
        public readonly string $completeUrl,
        string $suffixUrl,
    ) {
        $this->createdAt = new \DateTimeImmutable();
        $this->id = $this->generateUlidHexCode();
        $this->shortUrl = $this->generateShortUrlWithSuffix($suffixUrl);
    }

    private function generateUlidHexCode(): string
    {
      return (new Ulid())
        ->toHex();
    }

    private function generateShortUrlWithSuffix(string $suffixUrl): string { 
        return "$suffixUrl/{$this->id}";
    }

    public function generateQrCode()
    {
      return QrCode::create($this->shortUrl)
          ->setEncoding(new Encoding('UTF-8'))
          ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
          ->setSize(120)
          ->setMargin(0)
          ->setForegroundColor(new Color(0, 0, 0))
          ->setBackgroundColor(new Color(255, 255, 255));
    }

    public function wasAccessedIn(\DateTimeImmutable $date = new \DateTimeImmutable()): void
    {
      $this->accesses[] = $date->format('y-m-d / G:i:s');
    }

    public function accesses(): ArrayCollection
    {
      return new ArrayCollection($this->accesses);
    }
}

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
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UrlRepository::class)]
class Url
{
    #[ORM\Id, ORM\Column(length: 12, unique: true)]
    #[Assert\NotBlank]
    public readonly string $id;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    public readonly \DateTimeImmutable $createdAt;
    
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $accesses = [];
    
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank, 
      Assert\Url(protocols: ['http', 'https'], 
      message: 'Url must be an valid site and respect http or https protocols'), 
      Assert\Length(min: 10, 
      minMessage: 'Your url must be at least 10 characters long')]
    public readonly string $completeUrl;

    #[ORM\Column(length: 128)]
    public readonly string $shortUrl;

    #[Assert\Type('string'), Assert\Length(min: 0, max: 32)]
    private string $label;

    private const FIST_CHAR_AFTER_HEX_NUMBER_PREFIX = 3;
    private const NUMBER_OF_TENTH_CHAR = 12;
    
    public function __construct(string $url,string $urlPrefix, ?string $label) {
        $this->completeUrl = $url;
        $this->label = $this->sanitizeLabel($label);
        $this->createdAt = new \DateTimeImmutable();
        $this->defineIdCode();
        $this->defineShortUrlWithPrefixUrlAndLabel($urlPrefix, $label);
    }

    private function sanitizeLabel(string $label): string
    {
        $labelWithoutSpecialChars = htmlspecialchars($label);
        return str_replace([' ', '/', '\\', '.'], '_', $labelWithoutSpecialChars);
    }

    private function defineIdCode(): void
    {
        $this->id = substr(
            (new Ulid())->toHex(), 
            self::FIST_CHAR_AFTER_HEX_NUMBER_PREFIX, 
            self::NUMBER_OF_TENTH_CHAR
        );
    }

    private function defineShortUrlWithPrefixUrlAndLabel(string $url, ?string $label): void { 
        $this->shortUrl = !empty($label) 
          ? "$url/{$this->label}.{$this->id}" 
          : "$url/{$this->id}";
    }

    public function wasAccessed(\DateTimeImmutable $date = new \DateTimeImmutable()): void
    {
        $this->accesses[] = $date->format(\DateTimeImmutable::W3C);
    }

    public function lastAccesses(): ArrayCollection
    {
        return new ArrayCollection($this->accesses);
    }

    public function generateQrCode(): QrCode
    {
        return QrCode::create($this->shortUrl)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(120)
            ->setMargin(0)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
    }
}

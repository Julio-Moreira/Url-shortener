<?php
namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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

    #[ORM\ManyToOne(inversedBy: 'urls')]
    private ?User $user = null;

    private const FIST_CHAR_AFTER_HEX_NUMBER_PREFIX = 3;
    private const NUMBER_OF_TENTH_CHAR = 12;
    
    public function __construct(string $url,string $urlPrefix, ?string $label = '', ?User $user = null) {
        $this->user = $user;
        $this->completeUrl = $url;
        $this->label = $this->sanitizeLabel($label);
        $this->createdAt = new \DateTimeImmutable();
        $this->defineIdCode();
        $this->defineShortUrlWithPrefixUrl($urlPrefix);
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

    private function defineShortUrlWithPrefixUrl(string $url): void { 
        $this->shortUrl = !empty($this->label) 
          ? "$url/{$this->label}.{$this->id}" 
          : "$url/{$this->id}";
    }

    public function wasAccessed(\DateTimeImmutable $date = new \DateTimeImmutable()): void
    {
        array_push(
            $this->accesses, 
            $date->format(\DateTimeImmutable::W3C));
    }

    public function lastAccesses(): Collection
    {
        return new ArrayCollection($this->accesses);
    }

    public function generateUrlQrCode(PngWriter $writer)
    {
        $finalQrCode = $writer->write(
            qrCode: $this->createRawQrCode(),
            label: $this->createLabelQrCode()
        );

        return $finalQrCode->getDataUri();
    }
    
    public function createRawQrCode(): QrCode
    {
        return QrCode::create($this->shortUrl)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(120)
            ->setMargin(0)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
    }
    
    public function createLabelQrCode(): Label
    {
        $font = new NotoSans(9);
        if (is_null($this->label))
            return new Label('Short Url', $font);
        
        return new Label($this->label, $font);
    }

    public function toArray(): array
    {
        return [
            'original_url' => $this->completeUrl,
            'short_url' => $this->shortUrl,
            'qr_code_url' => "{$this->shortUrl}/qrCode",
            "user_email" => $this->user->getEmail(),
            'total_accesses' => $this->lastAccesses()->count(),
            'created_at' => $this->createdAt->format(\DateTimeImmutable::W3C),
        ]; 
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}

<?php

namespace App\Controller;

use App\Entity\Url;
use App\Entity\User;
use App\Repository\UrlRepositoryInterface;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UrlController extends AbstractController
{

    public function __construct(
        private UrlRepositoryInterface $urlRepository,
        private ValidatorInterface $validator,
        private PngWriter $writer,
    ) { }

    public function show(string $labelWithId): Response
    {
        $url = $this->urlRepository->get($labelWithId);
        if (is_null($url)) 
            return new JsonResponse(['message' => 'short url not exist'], 404);

        $url->wasAccessed();
        return new JsonResponse([
            'original_url' => $url->completeUrl,
            'short_url' => $url->shortUrl,
            'qr_code_url' => "{$url->shortUrl}/qrCode",
            'total_accesses' => $url->lastAccesses()->count(),
            'last_access' => $url->lastAccesses()->last(),
            'created_at' => $url->createdAt->format(\DateTimeImmutable::W3C),
        ]);
    }

    public function showQrCode(string $labelWithId, #[CurrentUser] ?User $user): Response
    {
        $url = $this->urlRepository->get($labelWithId);
        if (is_null($url)) 
            return new JsonResponse(['message' => 'short url not exist'], 404);
            
        $generatedQrCode = $url->generateQrCode();
        $urlQrCode = $this->writer->write(
            $generatedQrCode
        )->getDataUri();

        $templatePath = 'qr_code_generator/index.html.twig';
        return $this->render($templatePath, ['urlQrCode' => $urlQrCode]);
    }

    public function store(Request $request): Response
    {
        $url = new Url(
            $request->get('url'),
            $this->getParameter('api_url'),
            $request->get('label')
        );
        
        $urlValidated = $this->validator->validate($url);
        if (count($urlValidated) > 0)
            return new JsonResponse(['message' => (string) $urlValidated], Response::HTTP_BAD_REQUEST);

        $this->urlRepository->save($url);
        return new JsonResponse([
            'short_url' => $url->shortUrl,
            'original_url' => $url->completeUrl,
            'qr_code_url' => "{$url->shortUrl}/qrCode",
            'created_at' => $url->createdAt->format(\DateTimeImmutable::W3C)
        ]);
    }

    public function destroy(string $labelWithId): Response
    {
        $url = $this->urlRepository->get($labelWithId);
        $this->urlRepository->remove($url);
        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}

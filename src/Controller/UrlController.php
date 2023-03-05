<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepositoryInterface;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UrlController extends AbstractController
{

    public function __construct(
        private UrlRepositoryInterface $urlRepository,
        private PngWriter $writer
    ) { }

    public function show(Url $url): Response
    {
        $url->wasAccessedIn();
        return new JsonResponse([
            'original_url' => $url->completeUrl,
            'short_url' => $url->shortUrl,
            'created_at' => $url->createdAt->format('y-m-d / G:i:s'),
            'total_accesses' => $url->accesses()->count()
        ]);
    }

    public function showQrCode(Url $url): Response
    {
        $url->wasAccessedIn();
        $urlQrCodeClass = $url->generateQrCode();
        $qrCode = $this->writer->write(
            $urlQrCodeClass
        )->getDataUri();

        return $this->render(
            'qr_code_generator/index.html.twig', 
            ['urlQrCode' => $qrCode]
        );
    }

    public function store(Request $request): Response
    {
        $url = new Url(
            $request->get('url'), 
            "{$this->getParameter('app_url')}/url"
        );
        $this->urlRepository->save($url);

        return new JsonResponse([
            'short_url' => $url->shortUrl,
            'original_url' => $url->completeUrl,
            'created_at' => $url->createdAt->format('y-m-d / G:i:s')
        ]);
    }

    public function destroy(Url $url): Response
    {
        $this->urlRepository
            ->remove($url);

        return new Response(status: 204);
    }
}

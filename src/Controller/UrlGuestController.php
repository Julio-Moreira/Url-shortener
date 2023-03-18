<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UrlRepositoryInterface;
use App\Entity\Url;
use Symfony\Component\Validator\ConstraintViolationList;

class UrlGuestController extends AbstractController
{
    public function __construct(
        private UrlRepositoryInterface $urlRepository,
        private ValidatorInterface $validator,
    ) { }
   
    public function show(string $labelWithId): Response
    {
        $url = $this->urlRepository->get($labelWithId);
        if ($url === null) return new JsonResponse(['message' => 'short url not exist'], Response::HTTP_NOT_FOUND);

        $url->wasAccessed();
        return new JsonResponse([
            'original_url' => $url->completeUrl,
            'short_url' => $url->shortUrl,
            'last_access' => $url->lastAccesses()->get(2),
            'created_at' => $url->createdAt->format(\DateTimeImmutable::W3C),
        ]);
    }

    public function store(Request $request): Response
    {
        $url = new Url($request->get('url'), $this->getParameter('api_guest_url'));
        
        $urlViolations = $this->validator->validate($url);
        if ($urlViolations->count() > 0)
            return new JsonResponse(
                $this->generateArrayOfViolationsMessages($urlViolations), Response::HTTP_BAD_REQUEST);
            
        $this->urlRepository->save($url);
        return new JsonResponse([
            'short_url' => $url->shortUrl,
            'original_url' => $url->completeUrl,
            'qr_code_url' => "{$url->shortUrl}/qrCode",
            'created_at' => $url->createdAt->format(\DateTimeImmutable::W3C)
        ]);
    }

    private function generateArrayOfViolationsMessages(ConstraintViolationList $violations): array
    {
        $violationsMessage = [];
        foreach ($violations as $violation)
            $violationsMessage[] = $violation->getMessage();
        
        return $violationsMessage;
    }
}

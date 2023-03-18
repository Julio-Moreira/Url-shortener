<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UrlController extends AbstractController
{

    public function __construct(
        private UrlRepositoryInterface $urlRepository,
        private UserRepositoryInterface $userRepository,
        private ValidatorInterface $validator,
        private PngWriter $writer,
    ) { }

    public function index(Request $request): Response
    {
        $user = $this->userRepository->get($request->get('email'));
        $userUrls = array_map(
            fn(Url $url) => $url->shortUrl, 
            $user->getUrls()->toArray());

        return new JsonResponse($userUrls);
    }

    public function show(string $shortUrl): Response
    {
        $url = $this->urlRepository->get($shortUrl);
        if (is_null($url)) 
            return new JsonResponse(
                ['message' => 'short url not exist'], Response::HTTP_NOT_FOUND);

        $url->wasAccessed();
        return new JsonResponse($url->toArray());
    }

    public function showQrCode(string $shortUrl): Response
    {
        $url = $this->urlRepository->get($shortUrl);
        if (is_null($url)) 
            return new JsonResponse(
                ['message' => 'short url not exist'], Response::HTTP_NOT_FOUND);

        $urlQrCode = $url->generateUrlQrCode($this->writer);
        return $this->render(
            'qr_code_generator/index.html.twig', compact($urlQrCode));
    }

    public function store(Request $request): Response
    {
        $user = $this->userRepository->get($request->get('email'));
        $url = new Url(
            $request->get('url'), $this->getParameter('api_url'), 
            $request->get('label'), $user);
        
        $urlViolations = $this->validator->validate($url);
        if ($urlViolations->count() > 0)
            return new JsonResponse(
                $this->generateArrayOfViolationsMessages($urlViolations), Response::HTTP_BAD_REQUEST);

        $this->urlRepository->save($url);
        return new JsonResponse($url->toArray());
    }

    private function generateArrayOfViolationsMessages(ConstraintViolationList $violations): array
    {
        $violationsMessage = [];
        foreach ($violations as $violation)
            $violationsMessage[] = $violation->getMessage();
        
        return $violationsMessage;
    }

    public function destroy(string $shortUrl): Response
    {
        $url = $this->urlRepository->get($shortUrl);
        $this->urlRepository->remove($url);
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}

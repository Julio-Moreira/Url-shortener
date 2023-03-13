<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
    ) { }
    
    public function register(Request $request): Response
    {
        $user = new User();
        $user->setEmail($request->get('email'));
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user, $request->get('password')
            )
        );

        $this->userRepository->save($user);
        return new JsonResponse(['message' => 'The registration was successfully completed']);
    }

    public function delete(Request $request)
    {
        $user = $this->userRepository->findOneBy(['email' => $request->get('email')]);
        if (is_null($user) || !$this->passwordHasher
                                   ->isPasswordValid($user, $request->get('password')))
            return new JsonResponse(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
        
        $this->userRepository->remove($user);
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}

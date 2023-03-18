<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepositoryInterface $userRepository,
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
        $user = $this->userRepository->get($request->get('email'));

        $userPasswordIsValid = $this->passwordHasher->isPasswordValid($user, $request->get('password'));
        if ($user === null || !$userPasswordIsValid) 
            return new JsonResponse(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
        
        $this->userRepository->remove($user);
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}

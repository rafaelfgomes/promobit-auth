<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokenGenerator
{
    private $em;
    private $jwtManager;

    public function __construct(EntityManagerInterface $em, JWTTokenManagerInterface $jwtManager)
    {
        $this->em = $em;
        $this->jwtManager = $jwtManager;
    }

    public function generate(User $user, array $payload)
    {
        $jwtToken = $this->jwtManager->createFromPayload($user, $payload);

        $tokenHash = md5($jwtToken);

        $user->setApiToken($tokenHash);

        $this->em->persist($user);
        $this->em->flush();

        return $jwtToken;
    }
}

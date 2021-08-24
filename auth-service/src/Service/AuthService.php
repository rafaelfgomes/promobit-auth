<?php

namespace App\Service;

use App\Entity\User;
use DateTime;
use App\Repository\AuthLoggerRepository;
use App\Repository\UserRepository;
use App\Security\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthService
{
  private $tokenGenerator;
  private $passwordEncoder;
  private $authLogger;
  private $em;

  public function __construct(TokenGenerator $tokenGenerator, UserPasswordEncoderInterface $passwordEncoder, AuthLoggerRepository $authLogger, EntityManagerInterface $em)
  {
    $this->tokenGenerator = $tokenGenerator;
    $this->passwordEncoder = $passwordEncoder;
    $this->authLogger = $authLogger;
    $this->em = $em;
  }

  public function checkCredentials(array $data): bool
  {
    $user = $this->em->getRepository(User::class)->findOneBy([ 'email' => $data['email'] ]);

    if (!$user) {
      return false;
    }

    if ($this->passwordEncoder->isPasswordValid($user, $data['password'])) {
      return true;
    }

    return false;
  }

  public function generateToken(string $email): string
  {
    $user = $this->em->getRepository(User::class)->findOneBy([ 'email' => $email ]);
    
    $payload = $this->generatePayload($email);
    
    return $this->tokenGenerator->generate($user, $payload);
  }

  public function generatePayload(string $email): array
  {
    return [
      'user' => $email,
      'exp' => (new DateTime())->modify('+5 minutes')->getTimestamp()
    ];
  }

  public function logAuth(string $email, string $token): void
  {
    $data = [
      'email' => $email,
      'token' => $token
    ];

    $this->authLogger->storeAuthInfo($data);
  }
}
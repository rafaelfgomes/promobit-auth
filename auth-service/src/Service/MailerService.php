<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Message\ForgotPasswordEmail;
use App\Repository\PasswordRepository;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MailerService
{
    private $userRepository;
    private $passwordRepository;
    private $bus;

    public function __construct(UserRepository $userRepository, PasswordRepository $passwordRepository, MessageBusInterface $bus)
    {
        $this->userRepository = $userRepository;
        $this->passwordRepository = $passwordRepository;
        $this->bus = $bus;
    }

    public function sendPasswordReset(string $email): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
          throw new NotFoundHttpException('Usuário não encontrado');
        }

        $hash = bin2hex(random_bytes(32));
        $url = $_ENV['API_URL'] . 'auth/password/reset/' . $hash;

        $email = (new ForgotPasswordEmail())->setEmail($user->getEmail())->setUrl($url);

        $this->passwordRepository->storeHash($user->getEmail(), $hash);
        
        $this->bus->dispatch($email);

        return true;
    }
}

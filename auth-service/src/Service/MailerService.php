<?php

namespace App\Service;

use App\Repository\PasswordResetRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{
    private $userRepository;
    private $passwordResetRepository;
    private $mailer;

    public function __construct(UserRepository $userRepository, MailerInterface $mailer, PasswordResetRepository $passwordResetRepository)
    {
        $this->userRepository = $userRepository;
        $this->passwordResetRepository = $passwordResetRepository;
        $this->mailer = $mailer;
    }

    public function sendPasswordReset(string $email): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
          throw new NotFoundHttpException('Usuário não encontrado');
        }

        $hash = bin2hex(random_bytes(32));

        $this->passwordResetRepository->storeHash($user->getEmail(), $hash);

        $url = $_ENV['API_URL'] . 'auth/password/reset/' . $hash;

        $email = (new Email())->from(Address::create('Suporte Promobit <suporte@promobit.com>'))->to($user->getEmail())->subject('Reset de senha')->html('<p>Link de reset de senha:<br><br>' . $url . '</p>');

        $this->mailer->send($email);

        return true;
    }
}

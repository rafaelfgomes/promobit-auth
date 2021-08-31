<?php

namespace App\MessageHandler;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Message\ForgotPasswordEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ForgotPasswordEmailHandler implements MessageHandlerInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
      $this->mailer = $mailer;
    }
    
    /**
     * @param ForgotPasswordEmail $forgotPasswordEmail
     * @return void
     */
    public function __invoke(ForgotPasswordEmail $forgotPasswordEmail): void
    {
        $email = (new Email())->from(Address::create('Suporte Promobit <suporte@promobit.com>'))->to($forgotPasswordEmail->getEmail())->subject('Reset de senha')->html('<p>Link de reset de senha:<br><br>' . $forgotPasswordEmail->getUrl() . '</p>');

        $this->mailer->send($email);
    }
}

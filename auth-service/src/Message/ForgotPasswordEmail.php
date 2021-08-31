<?php

namespace App\Message;

class ForgotPasswordEmail
{
  private $email;
  private $url;

  public function getUrl(): string
  {
    return $this->url;
  }

  public function setUrl(string $url): self
  {
    $this->url = $url;

    return $this;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }
}
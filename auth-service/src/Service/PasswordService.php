<?php

namespace App\Service;

use App\Repository\PasswordRepository;

class PasswordService
{
  private $passwordRepository;

  public function __construct(PasswordRepository $passwordRepository)
  {
    $this->passwordRepository = $passwordRepository;
  }

  public function resetPassword(string $hash, string $newPassword): bool
  {
    return $this->passwordRepository->resetPassword($hash, $newPassword);
  }
}
<?php

namespace App\Service;

use App\Repository\PasswordResetRepository;

class PasswordResetService
{
  private $passwordResetRepository;

  public function __construct(PasswordResetRepository $passwordResetRepository)
  {
    $this->passwordResetRepository = $passwordResetRepository;
  }

  public function resetPassword(string $hash, string $newPassword): bool
  {
    return $this->passwordResetRepository->resetPassword($hash, $newPassword);
  }
}
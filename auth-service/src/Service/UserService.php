<?php

namespace App\Service;

use App\Repository\UserRepository;

class UserService
{

    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function all(): ?array
    {
        $users = $this->userRepository->getAllUsers();

        return $users;
    }

    public function getOne(int $id): array
    {
        $user = $this->userRepository->getOneUser($id);

        return $user;
    }

    public function update(array $data, int $id): array
    {
        $userUpdated = $this->userRepository->updateUser($data, $id);

        return $userUpdated;
    }

    public function inactivate(int $id): array
    {
        $userInactive = $this->userRepository->inactivateUser($id);

        return $userInactive;
    }
}

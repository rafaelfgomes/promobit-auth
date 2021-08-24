<?php

namespace App\Controller;

use ErrorException;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends AbstractController
{
    private $userService;

    public function __construct(UserService $userService)   
    {
        $this->userService = $userService;
    }

    /**
     * @Route("/users", name="all_users", methods={"GET"})
     */
    public function all(): JsonResponse
    {
        try {
            $users = $this->userService->all();
        } catch (\Exception $e) {
            return new JsonResponse([ 'error' => $e->getMessage(), Response::HTTP_BAD_REQUEST ]);
        }

        if (empty($users)) {
            throw new NotFoundHttpException('Nenhum usuário encontrado');
        }

        return new JsonResponse($users);
    }

    /**
     * @Route("/users/{id}", name="get_user", methods={"GET"})
     */
    public function getOne(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getOne($id);
        } catch (\Exception $e) {
            return new JsonResponse([ 'error' => $e->getMessage(), Response::HTTP_BAD_REQUEST ]);
        }

        if (!$user) {
            throw $this->createNotFoundException('Usuário não encontrado');
        }

        return new JsonResponse($user);
    }

    /**
     * @Route("/users/{id}", name="update_user", methods={"PUT", "PATCH"})
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->request->all();

            $userUpdated = $this->userService->update($data, $id);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        if (!$userUpdated) {
            throw new ErrorException('Erro ao atualizar o usuário');
        }

        return new JsonResponse($userUpdated);
    }

    /**
     * @Route("/users/{id}", name="inactivate_user", methods={"DELETE"})
     */
    public function inactivate(int $id): JsonResponse
    {
        try {
            $userInactivated = $this->userService->inactivate($id);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        if (!$userInactivated) {
            throw new ErrorException('Erro ao inativar o usuário');
        }

        return new JsonResponse($userInactivated);
    }
}

<?php

namespace App\Controller;

use ErrorException;
use App\Service\AuthService;
use App\Service\MailerService;
use App\Service\PasswordService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthController extends AbstractController
{
    private $authService;
    private $mailerService;
    private $passwordService;
    
    public function __construct(AuthService $authService, MailerService $mailerService, PasswordService $passwordService)    
    {
        $this->authService = $authService;
        $this->mailerService = $mailerService;
        $this->passwordService = $passwordService;
    }

    /**
     * @Route("/auth/login", name="login", methods={"POST"})
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->request->all();
        
        $isCredentialsOk = $this->authService->checkCredentials($credentials);

        if (!$isCredentialsOk) {
            $error = [
                'message' => 'Usuário ou senha incorretos'
            ];

            return new JsonResponse($error, Response::HTTP_NOT_FOUND);
        }

        $token = $this->authService->generateToken($credentials['email']);

        $this->authService->logAuth($credentials['email'], $token);

        $response = [
            'type' => 'Bearer',
            'token' => $token
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/auth/register", name="register_user", methods={"POST"})
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->request->all();

            $userCreated = $this->authService->store($data);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        if (!$userCreated) {
            throw new ErrorException('Erro ao criar o usuário');
        }

        return new JsonResponse($userCreated, Response::HTTP_CREATED);
    }

    /**
     * @Route("/auth/forgot-password", name="forgot_password", methods={"POST"})
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $email = $request->request->get('email');

        try {
            $emailSent = $this->mailerService->sendPasswordReset($email);

            if (!$emailSent) {
                return new NotFoundHttpException('Erro');
            }

        } catch (\Exception $e) {
            $exception = [
                'error' => $e->getMessage()
            ];

            return new JsonResponse($exception, Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([ 'message' => 'Email enviado com sucesso!!' ]);
    }

    /**
     * @Route("/auth/password/reset/{hash}", name="update_password", methods={"POST"})
     */
    public function passwordReset(Request $request, string $hash): JsonResponse
    {
        $newPassword = $request->request->get('password');

        $isPasswordReseted = $this->passwordService->resetPassword($hash, $newPassword);

        if (!$isPasswordReseted) {
            $errors['error'] = [ 
                'message' => 'Erro ao resetar a senha!!'
            ];

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([ 'message' => 'Senha atualizada com sucesso' ]);
    }
}

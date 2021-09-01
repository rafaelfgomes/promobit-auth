<?php

namespace App\Security;

use App\Document\AuthLogger;
use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JwtAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $params;
    private $dm;

    public function __construct(EntityManagerInterface $em, DocumentManager $dm, ContainerBagInterface $params)
    {
        $this->em = $em;
        $this->params = $params;
        $this->dm = $dm;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
      $data = [ 
        'message' => 'Autenticação requerida'
      ];

      return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request)
    {
      return $request->headers->has('Authorization');
    }

    public function getCredentials(Request $request)
    {
      return $request->headers->get('Authorization');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
      try {
        $token = str_replace('Bearer ', '', $credentials);

        $tokenParts = explode('.', $token);

        $tokenPayload = base64_decode($tokenParts[1]);

        $jwt = json_decode($tokenPayload);

        $authLogger = $this->dm->getRepository(AuthLogger::class);

        $lastToken = $authLogger->getLastLoginToken($jwt->user);

        if ($lastToken !== $token) {
          throw new AuthenticationException('Token expirado');
        }

        $user = $this->em->getRepository(User::class)->findOneBy([ 'email' => $jwt->user ]);
      }catch (\Exception $e) {
          throw new AuthenticationException($e->getMessage());
      }

      return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
      return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $e)
    {
      $error = [
        'message' => $e->getMessage()
      ];

      return new JsonResponse($error, Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
      return;
    }

    public function supportsRememberMe()
    {
      false;
    }
}

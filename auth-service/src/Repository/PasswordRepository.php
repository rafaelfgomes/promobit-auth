<?php

namespace App\Repository;

use DateInterval;
use DateTimeImmutable;
use App\Entity\Password;
use App\Entity\PasswordReset;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @method PasswordReset|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordReset|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordReset[]    findAll()
 * @method PasswordReset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordRepository extends ServiceEntityRepository
{
    private $userRepository;
    private $passwordEncoder;

    public function __construct(ManagerRegistry $registry, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct($registry, PasswordReset::class);
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function resetPassword(string $hash, string $newPassword): bool
    {
        $now = new DateTimeImmutable();

        $passwordReset = $this->findOneBy([ 'hash' => $hash ]);

        if ($passwordReset) {

            if ($now > $passwordReset->getExpiresAt()) {
                throw new NotFoundHttpException('Token expirado');
            }

            $user = $this->userRepository->findOneBy([ 'email' => $passwordReset->getEmail() ]);

            if (!$user) {
                throw new NotFoundHttpException('Usuário não encontrado');
            }

            $user->setPassword($this->passwordEncoder->encodePassword($user, $newPassword))->setUpdatedAt(new DateTimeImmutable());

            $this->_em->remove($passwordReset);
            $this->_em->flush();

            $this->_em->persist($user);
            $this->_em->flush();

            return true;
        }

        throw new NotFoundHttpException('Token inválido');
    }

    public function storeHash(string $email, string $hash): void
    {
        $passwd = new Password();

        $passwd->setEmail($email)
                ->setHash($hash)
                ->setExpiresAt((new DateTimeImmutable())->add(new DateInterval('PT2H')));

        $this->_em->persist($passwd);
        $this->_em->flush();
    }
}

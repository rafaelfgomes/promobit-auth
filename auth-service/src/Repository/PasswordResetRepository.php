<?php

namespace App\Repository;

use DateTimeImmutable;
use App\Entity\PasswordReset;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\AST\Functions\DateDiffFunction;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @method PasswordReset|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordReset|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordReset[]    findAll()
 * @method PasswordReset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordResetRepository extends ServiceEntityRepository
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

        //dd($now, $passwordReset);
        //dd($now > $passwordReset->getExpiresAt());

        if ($passwordReset) {

            if ($now > $passwordReset->getExpiresAt()) {
                throw new NotFoundHttpException('Token expirado');
            }

            $user = $this->userRepository->findOneBy([ 'email' => $passwordReset->getEmail() ]);

            if (!$user) {
                throw new NotFoundHttpException('Usuário não encontrado');
            }

            $user->setPassword($this->passwordEncoder->encodePassword($user, $newPassword));
            $user->setUpdatedAt(new DateTimeImmutable());

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
        $passwd = new PasswordReset();

        $passwd->setEmail($email);
        $passwd->setHash($hash);
        $passwd->setExpiresAt((new DateTimeImmutable())->add(new DateInterval('PT2H')));

        $this->_em->persist($passwd);
        $this->_em->flush();
    }
}

<?php

namespace App\Repository;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private $passwordEncoder;

    public function __construct(ManagerRegistry $registry, UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct($registry, User::class);
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function getAllUsers(): ?array
    {
        $users = $this->findAll();

        if ($users) {
            $userCollection['users'] = [];

            foreach ($users as $user) {
                $userCollection['users'][] = $user->toArray();
            }

            return $userCollection;
        }
        
        return null;
    }

    public function getOneUser(int $id): ?array
    {
        $user = $this->findOneBy($id);

        if ($user) {
            return $user->toArray();
        }

        return null;
    }

    public function storeUser(array $data): array
    {
        $user = new User();

        $user
            ->setName($data['name'])
            ->setEmail($data['email'])
            ->setPassword($this->passwordEncoder->encodePassword($user, $data['password']))
            ->setActive(true)
            ->setCreatedAt(new DateTimeImmutable());

        $this->_em->persist($user);
        $this->_em->flush();

        return $user->toArray();
    }

    public function updateUser(array $data, int $id): ?array
    {
        $user = $this->findOneBy(['id' => $id]);

        if ($user) {
            $user->setName($data['name'])->setUpdatedAt(new DateTimeImmutable());
            $this->_em->persist($user);
            $this->_em->flush();
            return $user->toArray();
        }

        return null;
    }

    public function inactivateUser(int $id): ?array
    {
        $user = $this->findOneBy($id);

        if ($user) {
            $user->setActive(false)->setUpdatedAt(new DateTimeImmutable());
            $this->_em->persist($user);
            $this->_em->flush();
            return $user->toArray();
        }

        return null;
    }
}

<?php

namespace App\Repository;

use DateTime;
use App\Document\AuthLogger;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

class AuthLoggerRepository extends ServiceDocumentRepository
{
    protected $dm;

    public function __construct(DocumentManager $dm, ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthLogger::class);
        $this->dm = $dm;
    }

    public function storeAuthInfo(array $data): void
    {
        $filters = $this->setFilters([ 'email' => $data['email'] ]);
        $query = $filters->getQuery();
        $logger = $query->getSingleResult();

        if (empty($logger)) {
            $authLogger = (new AuthLogger())
                            ->setEmail($data['email'])
                            ->setToken($data['token'])
                            ->setlLastLoggedIn(new DateTime());

            $this->dm->persist($authLogger);
            
            $this->dm->flush();
            
            return;
        }

        $filters
            ->findAndUpdate()
            ->returnNew()
            ->field('token')->set($data['token'])
            ->field('lastLoggedIn')->set(new DateTime())
            ->getQuery()
            ->execute();
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->dm->createQueryBuilder(AuthLogger::class);
    }

    public function setFilters(array $filters)
    {
        $qb = $this->getQueryBuilder();

        foreach ($filters as $key => $filter) {
            $qb->field($key)->equals($filter);
        }

        return $qb;
    }

    public function getLastLoginToken(string $email): ?string
    {
        $document = $this->setFilters(['email' => $email])->select('token')->getQuery()->execute();
        return $document->current()->getToken() ?? null;
    }
}

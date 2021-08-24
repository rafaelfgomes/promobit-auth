<?php

namespace App\Repository;

use App\Document\AuthLogger;
use DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class AuthLoggerRepository extends DocumentRepository
{
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function storeAuthInfo(array $data): void
    {
        $logger = new AuthLogger();

        $logger->setEmail($data['email'])->setToken($data['token'])->setLoggedIn(new DateTime());

        $this->dm->persist($logger);
        $this->dm->flush();
    }
}

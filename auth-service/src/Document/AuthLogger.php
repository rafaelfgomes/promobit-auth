<?php

namespace App\Document;

use DateTime;
use App\Repository\AuthLoggerRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(repositoryClass=AuthLoggerRepository::class, collection="auth_log")
 */
class AuthLogger
{
    /**
     * @MongoDB\Id(type="string")
     * @var string
     */
    private $id;

    /**
     * @MongoDB\Field(type="string")
     * @var string
     */
    private $email;

    /**
     * @MongoDB\Field(type="string")
     * @var string
     */
    private $token;

    /**
     * @MongoDB\Field(type="date")
     * @var DateTime
     */
    private $lastLoggedIn;

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return self
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }
    
    /**
     * @return DateTime|null
     */
    public function getLastLoggedIn(): ?DateTime
    {
        return $this->lastLoggedIn;
    }

    /**
     * @param DateTime $loggedIn
     * @return self
     */
    public function setlLastLoggedIn(DateTime $lastLoggedIn): self
    {
        $this->lastLoggedIn = $lastLoggedIn;

        return $this;
    }
}

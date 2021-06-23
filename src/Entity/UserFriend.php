<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class UserFriend extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $friend;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private $requestAccepted = false;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getFriend()
    {
        return $this->friend;
    }

    /**
     * @param mixed $friend
     */
    public function setFriend($friend): void
    {
        $this->friend = $friend;
    }

    /**
     * @return bool
     */
    public function isRequestAccepted(): bool
    {
        return $this->requestAccepted;
    }

    /**
     * @param bool $requestAccepted
     */
    public function setRequestAccepted(bool $requestAccepted): void
    {
        $this->requestAccepted = $requestAccepted;
    }
}

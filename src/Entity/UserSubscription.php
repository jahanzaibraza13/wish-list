<?php

namespace App\Entity;

use App\Repository\UserSubscriptionRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserSubscriptionRepository::class)
 */
class UserSubscription extends AbstractEntity
{
    /**
     * @ORM\Column(type="datetime")
     */
    private $subscriptionTime;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="userSubscription", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @return DateTimeInterface|null
     */
    public function getSubscriptionTime(): ?DateTimeInterface
    {
        return $this->subscriptionTime;
    }

    /**
     * @param DateTimeInterface $subscriptionTime
     * @return $this
     */
    public function setSubscriptionTime(DateTimeInterface $subscriptionTime): self
    {
        $this->subscriptionTime = $subscriptionTime;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}

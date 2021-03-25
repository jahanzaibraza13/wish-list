<?php

namespace App\Repository;

use App\Entity\AccessToken;
use App\Entity\InviteClient;
use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class UserRepository
 * @package App\Repository
 */
class UserRepository extends AbstractRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ManagerRegistry $registry,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($registry, User::class);
        $this->entityManager = $entityManager;
    }


}
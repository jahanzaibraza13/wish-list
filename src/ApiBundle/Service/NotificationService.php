<?php

namespace App\ApiBundle\Service;

use App\ApiBundle\Enum\CommonEnum;
use App\Entity\Item;
use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Wishlist;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class NotificationService
 * @package App\ApiBundle\Service
 */
class NotificationService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var WishlistService
     */
    private $wishlistService;

    /**
     * UserService constructor.
     * @param UserService $userService
     * @param WishlistService $wishlistService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        UserService $userService,
        WishlistService $wishlistService,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
        $this->wishlistService = $wishlistService;
    }

    /**
     * @param User $user
     * @param array $data
     */
    public function getNotifications(User $user, array $data)
    {
        $perPage = empty($data['per_page']) ? CommonEnum::PER_PAGE_MAX : (int) $data['per_page'];
        $page = empty($data['page']) ? 1 : (int) $data['page'];
        if ($page <= 0) {
            $page = 1;
        }

        $qb = $this->entityManager->getRepository('App:Notification')->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.id', 'DESC');

        if (!empty($data['page'])) {
            $qb->setFirstResult(($page-1) * $perPage)->setMaxResults($perPage);
        }

        $notifications = $qb->getQuery()->getResult();

        $data = [];
        /** @var Notification $notification */
        foreach ($notifications as $notification) {
            $data[] = $this->makeNotificationData($notification);
        }

        return $data;
    }

    /**
     * @param Notification $notification
     * @return array|null
     */
    public function makeNotificationData(Notification $notification): ?array
    {
        if (empty($notification)) {
            return null;
        }

        return [
            'id' => $notification->getId(),
            'type' => $notification->getType(),
            'by_user' => $this->userService->makeUserDetail($notification->getByUser()),
            'user' => $this->userService->makeUserDetail($notification->getUser()),
            'wishlist' => $notification->getType() === CommonEnum::NOTIFICATION_TYPE_ADD_TO_WISHLIST ?
                $this->wishlistService->makeWishListData($this->wishlistService->getById($notification->getObjectId())) : null
        ];
    }

    /**
     * @param $id
     * @return object|null
     */
    public function getById($id)
    {
        return $this->entityManager->getRepository('App:Notification')->find($id);
    }

    /**
     * @param Notification $notification
     */
    public function deleteNotification(Notification $notification)
    {
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }
}

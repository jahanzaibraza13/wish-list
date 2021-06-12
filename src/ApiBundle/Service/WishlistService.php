<?php

namespace App\ApiBundle\Service;

use App\ApiBundle\Enum\CommonEnum;
use App\Entity\Item;
use App\Entity\User;
use App\Entity\Wishlist;
use App\Entity\WishlistUser;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class WishlistService
 * @package App\ApiBundle\Service
 */
class WishlistService
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
     * UserService constructor.
     * @param UserService $userService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(UserService $userService, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
    }

    /**
     * @param User $user
     * @param array $data
     * @return array
     */
    public function create(User $user, array $data): array
    {
        $wishList = new Wishlist();
        $wishList->setName($data['name']);
        $wishList->setCode($data['code']);
        $wishList->setUser($user);
        $this->entityManager->persist($wishList);
        $this->entityManager->flush();

        return $this->makeWishListData($wishList);
    }

    /**
     * @param Wishlist|null $wishlist
     * @param false $getMembers
     * @return array|null
     */
    public function makeWishListData(Wishlist $wishlist = null, $getMembers = false): ?array
    {
        if (empty($wishlist)) {
            return null;
        }

        $data = [
            'id' => $wishlist->getId(),
            'name' => $wishlist->getName(),
            'code' => $wishlist->getCode(),
            'user' => $this->userService->makeUserDetail($wishlist->getUser())
        ];

        if ($getMembers) {
            $data['members'] = [];
            $members = $this->entityManager->getRepository('App:WishlistUser')->findBy(['wishlist' => $wishlist]);
            /** @var WishlistUser $member */
            foreach ($members as $member) {
                $data['members'][] = $this->userService->makeUserDetail($member->getUser());
            }
        }

        return $data;
    }

    /**
     * @param $id
     * @return object|null
     */
    public function getById($id)
    {
        return $this->entityManager->getRepository('App:Wishlist')->find($id);
    }

    /**
     * @param $code
     * @return object|null
     */
    public function getByCode($code)
    {
        return $this->entityManager->getRepository('App:Wishlist')->findOneBy(['code' => $code]);
    }

    /**
     * @param Wishlist $wishlist
     * @param User $user
     * @param User $loggedInUser
     * @return bool|string
     */
    public function addWishlistUser(Wishlist $wishlist, User $user, User $loggedInUser)
    {
        $wishlistUser = $this->entityManager->getRepository('App:WishlistUser')->findOneBy([
            'user' => $user,
            'wishlist' => $wishlist
        ]);

        if (!empty($wishlistUser)) {
            return "User already member of wishlist.";
        }

        $wishlistUser = new WishlistUser();
        $wishlistUser->setWishlist($wishlist);
        $wishlistUser->setUser($user);
        $this->entityManager->persist($wishlistUser);
        $this->entityManager->flush();

        $this->entityManager->getRepository('App:Notification')->create(
            CommonEnum::NOTIFICATION_TYPE_ADD_TO_WISHLIST,
            $user,
            $loggedInUser,
            $wishlist->getId()
        );

        return true;
    }

    /**
     * @param Wishlist $wishlist
     * @param User $loggedInUser
     * @param User|null $targetUser
     * @return bool|string
     */
    public function removeWishlistUser(Wishlist $wishlist, User $loggedInUser, User $targetUser = null)
    {
        $userToRemove = $loggedInUser;
        if (!empty($targetUser)) {
            $userToRemove = $targetUser;
        }

        $wishlistUser = $this->entityManager->getRepository('App:WishlistUser')->findOneBy([
            'user' => $userToRemove,
            'wishlist' => $wishlist
        ]);

        if (empty($wishlistUser)) {
            return "User is not the member of wishlist.";
        }

        $this->entityManager->remove($wishlistUser);
        $this->entityManager->flush();

        $itemsWithUser = $this->entityManager->getRepository('App:Item')->findBy([
            'wishlist' => $wishlist,
            'user' => $userToRemove
        ]);

        /** @var Item $item */
        foreach ($itemsWithUser as $item) {
            $item->setUser(null);
        }

        $this->entityManager->flush();

        return true;
    }

    /**
     * @param User $user
     * @param $getMembers
     * @param $wishlistId
     * @return array
     */
    public function getWishlist(User $user, $getMembers, $wishlistId): array
    {
        $returnData = [];
        if (!empty($wishlistId)) {
            $wishlist = $this->getById($wishlistId);
            return $this->makeWishListData($wishlist, $getMembers);
        }
        $wishlists = $this->entityManager->getRepository('App:Wishlist')->findBy(['user' => $user]);
        /** @var Wishlist $wishlist */
        foreach ($wishlists as $wishlist) {
            $returnData[] = $this->makeWishListData($wishlist, $getMembers);
        }

        return $returnData;
    }

    /**
     * @param User $user
     * @param $getMembers
     * @param $wishlistId
     * @return array
     */
    public function getMemberWishlist(User $user, $getMembers, $wishlistId): array
    {
        $returnData = [];
        if (!empty($wishlistId)) {
            $wishlist = $this->getById($wishlistId);
            return $this->makeWishListData($wishlist, $getMembers);
        }
        $wishlists = $this->entityManager->getRepository('App:WishlistUser')->findBy(['user' => $user]);
        /** @var WishlistUser $wishlist */
        foreach ($wishlists as $wishlist) {
            $returnData[] = $this->makeWishListData($wishlist->getWishlist(), $getMembers);
        }

        return $returnData;
    }

    /**
     * @param Wishlist $wishlist
     */
    public function deleteWishlist(Wishlist $wishlist)
    {
        $this->entityManager->remove($wishlist);
        $this->entityManager->flush();
    }
}

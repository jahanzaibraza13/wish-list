<?php

namespace App\ApiBundle\Service;

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
     * @param string $name
     * @return array
     */
    public function create(User $user, string $name): array
    {
        $wishList = new Wishlist();
        $wishList->setName($name);
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
     * @param Wishlist $wishlist
     * @param User $user
     * @return bool|string
     */
    public function addWishlistUser(Wishlist $wishlist, User $user)
    {
        $wishlistUser = $this->entityManager->getRepository('App:WishlistUser')->findOneBy([
            'user' => $user,
            'wishlist' => $wishlist
        ]);

        if (!empty($wishlistUser)) {
            return "User already member of wishlist.";
        }

        $wishlistItems = $this->entityManager->getRepository('App:Item')->findBy(['wishlist' => $wishlist]);
        $wishlistMembers = $this->entityManager->getRepository('App:WishlistUser')->findBy(['wishlist' => $wishlist]);

        if (count($wishlistItems) <= count($wishlistMembers)) {
            return "There are not enough items in the wishlist.";
        }

        $wishlistUser = new WishlistUser();
        $wishlistUser->setWishlist($wishlist);
        $wishlistUser->setUser($user);
        $this->entityManager->persist($wishlistUser);
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
        $wishlists = $this->entityManager->getRepository('App:WishlistUser')->findBy(['user' => $user]);
        /** @var WishlistUser $wishlist */
        foreach ($wishlists as $wishlist) {
            $returnData[] = $this->makeWishListData($wishlist->getWishlist(), $getMembers);
        }

        return $returnData;
    }
}

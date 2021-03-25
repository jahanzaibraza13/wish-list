<?php

namespace App\ApiBundle\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Entity\Wishlist;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ItemService
 * @package App\ApiBundle\Service
 */
class ItemService
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
     * @param Wishlist $wishlist
     * @param array $data
     * @return array
     */
    public function create(Wishlist $wishlist, array $data): array
    {
        $item = new Item();
        $item->setName($data['name']);
        !empty($data['description']) ? $item->setDescription($data['description']) : null;
        $item->setWishlist($wishlist);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->makeItemData($item);
    }

    /**
     * @param Item|null $item
     * @return array|null
     */
    public function makeItemData(Item $item = null): ?array
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'description' => $item->getDescription(),
            'wishlist' => $this->wishlistService->makeWishListData($item->getWishlist()),
            'user' => $this->userService->makeUserDetail($item->getUser())
        ];
    }

    /**
     * @param $id
     * @return object|null
     */
    public function getById($id)
    {
        return $this->entityManager->getRepository('App:Item')->find($id);
    }

    /**
     * @param User $user
     * @param Item $item
     * @param $select
     * @return bool|string
     */
    public function linkItemWithUser(User $user, Item $item, $select)
    {
        if (!$this->checkIfUserMemberOfWishlist($user, $item->getWishlist())) {
            return "User is not the member of item wishlist.";
        }

        if (!empty($select) && $item->getUser() == $user) {
            return "User has already selected this item.";
        }

        $item->setUser(null);
        if (!empty($select)) {
            $item->setUser($user);
        }

        $this->entityManager->flush();

        return true;
    }

    /**
     * @param User $user
     * @param Wishlist $wishlist
     * @return bool
     */
    public function checkIfUserMemberOfWishlist(User $user, Wishlist $wishlist): bool
    {
        $wishlistUser = $this->entityManager->getRepository('App:WishlistUser')
            ->findOneBy(['user' => $user, 'wishlist' => $wishlist]);

        return !empty($wishlistUser);
    }

    /**
     * @param Wishlist $wishlist
     * @param $itemId
     * @return array
     */
    public function getWishlistItems(Wishlist $wishlist, $itemId): array
    {
        if (!empty($itemId)) {
            /** @var Item $item */
            $item = $this->entityManager->getRepository('App:Item')
                ->findOneBy(['wishlist' => $wishlist, 'id' => $itemId]);
            return $this->makeItemData($item);
        }

        $itemData = [];
        $items = $this->entityManager->getRepository('App:Item')->findBy(['wishlist' => $wishlist]);
        /** @var Item $item */
        foreach ($items as $item)
        {
            $itemData[] = $this->makeItemData($item);
        }

        return $itemData;
    }
}

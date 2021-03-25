<?php

namespace App\ApiBundle\Controller\V1;

use App\ApiBundle\Enum\CommonEnum;
use App\ApiBundle\Service\ItemService;
use App\ApiBundle\Service\UtilService;
use App\ApiBundle\Service\WishlistService;
use App\Entity\Item;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;

/**
 * Class ItemController
 * @package App\ApiBundle\Controller\V1
 */
class ItemController extends AbstractController
{
    /**
     * @Route(methods={"POST"}, path="/user/wishlist/{wishlist_id}/item", name="create_wishlist_item_api")
     *
     * @Operation(
     *     tags={"Item"},
     *     summary="Create wishlist item",
     *     @SWG\Parameter(
     *       type="string",
     *       name="Authorization",
     *       in="header",
     *       required=true,
     *       description="Bearer your_token, Use client token here"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="Success"
     *      ),
     *      @SWG\Response(
     *          response=400,
     *          description="Missing some required params"
     *      ),
     *      @SWG\Response(
     *          response=403,
     *          description="Invalid Token provided"
     *      ),
     *      @SWG\Response(
     *          response=500,
     *          description="Some server error"
     *      ),
     *      @SWG\Parameter(
     *          name="wishlist_id",
     *          in="path",
     *          type="integer",
     *          required=true,
     *          description="Wishlist id"
     *      ),
     *      @SWG\Parameter(
     *          name="name",
     *          in="formData",
     *          type="string",
     *          required=true,
     *          description="Name"
     *      ),
     *      @SWG\Parameter(
     *          name="description",
     *          in="formData",
     *          type="string",
     *          required=false,
     *          description="Description"
     *      )
     * )
     *
     * @param $wishlist_id
     * @param Request $request
     * @param WishlistService $wishlistService
     * @param ItemService $itemService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(
        $wishlist_id,
        Request $request,
        WishlistService $wishlistService,
        ItemService $itemService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            $data = $request->request->all();
            if (empty($data['name'])) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Name is required."
                );
            }

            $wishlist = $wishlistService->getById($wishlist_id);
            if (empty($wishlist)) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Wishlist not found."
                );
            }

            $item = $itemService->create($wishlist, $data);
            return $utilService->makeResponse(
                Response::HTTP_OK,
                "Item created successfully.",
                $item,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[create_wishlist_item_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }

    /**
     * @Route(methods={"GET"}, path="/user/wishlist/{wishlist_id}/items", name="get_wishlist_items_api")
     *
     * @Operation(
     *     tags={"Item"},
     *     summary="Get wishlist items",
     *     @SWG\Parameter(
     *       type="string",
     *       name="Authorization",
     *       in="header",
     *       required=true,
     *       description="Bearer your_token, Use client token here"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="Success"
     *      ),
     *      @SWG\Response(
     *          response=400,
     *          description="Missing some required params"
     *      ),
     *      @SWG\Response(
     *          response=403,
     *          description="Invalid Token provided"
     *      ),
     *      @SWG\Response(
     *          response=500,
     *          description="Some server error"
     *      ),
     *      @SWG\Parameter(
     *          name="wishlist_id",
     *          in="path",
     *          type="integer",
     *          required=true,
     *          description="Wishlist id"
     *      ),
     *      @SWG\Parameter(
     *          name="item_id",
     *          in="query",
     *          type="string",
     *          required=false,
     *          description="Item id"
     *      )
     * )
     *
     * @param $wishlist_id
     * @param Request $request
     * @param WishlistService $wishlistService
     * @param ItemService $itemService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAction(
        $wishlist_id,
        Request $request,
        WishlistService $wishlistService,
        ItemService $itemService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            $itemId = $request->get('item_id');
            $wishlist = $wishlistService->getById($wishlist_id);
            if (empty($wishlist)) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Wishlist not found."
                );
            }

            $itemData = $itemService->getWishlistItems($wishlist, $itemId);

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "",
                $itemData,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[get_wishlist_items_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }

    /**
     * @Route(methods={"POST"}, path="/user/item/{item_id}/select", name="select_item_api")
     *
     * @Operation(
     *     tags={"Item"},
     *     summary="Select item",
     *     @SWG\Parameter(
     *       type="string",
     *       name="Authorization",
     *       in="header",
     *       required=true,
     *       description="Bearer your_token, Use client token here"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="Success"
     *      ),
     *      @SWG\Response(
     *          response=400,
     *          description="Missing some required params"
     *      ),
     *      @SWG\Response(
     *          response=403,
     *          description="Invalid Token provided"
     *      ),
     *      @SWG\Response(
     *          response=500,
     *          description="Some server error"
     *      ),
     *      @SWG\Parameter(
     *          name="item_id",
     *          in="path",
     *          type="integer",
     *          required=true,
     *          description="Item id"
     *      ),
     *      @SWG\Parameter(
     *          name="is_select",
     *          in="formData",
     *          type="integer",
     *          required=false,
     *          enum={0,1}
     *      ),
     * )
     *
     * @param $item_id
     * @param Request $request
     * @param ItemService $itemService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function selectItemAction(
        $item_id,
        Request $request,
        ItemService $itemService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            $select = $request->request->get('is_select');
            /** @var Item $item */
            $item = $itemService->getById($item_id);
            if (empty($item)) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Item not found."
                );
            }

            if ($item->getUser() && $item->getUser() != $this->getUser()) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Item is already selected by a user."
                );
            }

            $response = $itemService->linkItemWithUser($this->getUser(), $item, $select);

            if ($response !== true) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    $response
                );
            }

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "Action performed successfully.",
                null,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[select_item_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }
}

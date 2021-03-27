<?php

namespace App\ApiBundle\Controller\V1;

use App\ApiBundle\Enum\CommonEnum;
use App\ApiBundle\Service\UserService;
use App\ApiBundle\Service\UtilService;
use App\ApiBundle\Service\WishlistService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;

/**
 * Class WishlistController
 * @package App\ApiBundle\Controller\V1
 */
class WishlistController extends AbstractController
{
    /**
     * @Route(methods={"POST"}, path="/user/wishlist", name="create_wishlist_api")
     *
     * @Operation(
     *     tags={"Wishlist"},
     *     summary="Create wish list",
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
     *          name="name",
     *          in="formData",
     *          type="string",
     *          required=true,
     *          description="Name"
     *      )
     * )
     *
     * @param Request $request
     * @param WishlistService $wishlistService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(
        Request $request,
        WishlistService $wishlistService,
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


            $wishlist = $wishlistService->create($this->getUser(), $data['name']);
            return $utilService->makeResponse(
                Response::HTTP_OK,
                "Wishlist created successfully.",
                $wishlist,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[create_wishlist_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }


    /**
     * @Route(methods={"POST"}, path="/user/wishlist/{wishlist_id}/user", name="create_wishlist_user_api")
     *
     * @Operation(
     *     tags={"Wishlist"},
     *     summary="Create wish list user",
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
     *          name="user_id",
     *          in="formData",
     *          type="integer",
     *          required=true,
     *          description="User id"
     *      )
     * )
     *
     * @param $wishlist_id
     * @param Request $request
     * @param WishlistService $wishlistService
     * @param UserService $userService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createWishlistUserAction(
        $wishlist_id,
        Request $request,
        WishlistService $wishlistService,
        UserService $userService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            $data = $request->request->all();
            if (empty($data['user_id'])) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "User id is required."
                );
            }

            $wishlist = $wishlistService->getById($wishlist_id);
            if (empty($wishlist)) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Wishlist not found."
                );
            }

            $user = $userService->getUserById($data['user_id']);
            if (empty($user)) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "User not found."
                );
            }

            $response = $wishlistService->addWishlistUser($wishlist, $user);
            if ($response !== true) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    $response
                );
            }

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "Action performed successfully.",
                [],
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[create_wishlist_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }


    /**
     * @Route(methods={"GET"}, path="/user/wishlist", name="get_wishlist_api")
     *
     * @Operation(
     *     tags={"Wishlist"},
     *     summary="Get wishlist",
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
     *          in="query",
     *          type="integer",
     *          required=false,
     *          description="Wishlist id"
     *      ),
     *      @SWG\Parameter(
     *          name="get_members",
     *          in="query",
     *          type="integer",
     *          required=false,
     *          enum={0,1}
     *      ),
     * )
     *
     * @param Request $request
     * @param UtilService $utilService
     * @param WishlistService $wishlistService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getWishlistAction(
        Request $request,
        UtilService $utilService,
        WishlistService $wishlistService,
        LoggerInterface $userLogger
    ) {
        try {
            $getMembers = !empty($request->get('get_members'));
            $wishlistId = $request->get('wishlist_id');
            $response = $wishlistService->getWishlist($this->getUser(), $getMembers, $wishlistId);

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "",
                $response,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[get_wishlist_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }

    /**
     * @Route(methods={"GET"}, path="/user/member-wishlist", name="get_member_wishlist_api")
     *
     * @Operation(
     *     tags={"Wishlist"},
     *     summary="Get member wishlist",
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
     *          in="query",
     *          type="integer",
     *          required=false,
     *          description="Wishlist id"
     *      ),
     *      @SWG\Parameter(
     *          name="get_members",
     *          in="query",
     *          type="integer",
     *          required=false,
     *          enum={0,1}
     *      ),
     * )
     *
     * @param Request $request
     * @param UtilService $utilService
     * @param WishlistService $wishlistService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getMemberWishlistAction(
        Request $request,
        UtilService $utilService,
        WishlistService $wishlistService,
        LoggerInterface $userLogger
    ) {
        try {
            $getMembers = !empty($request->get('get_members'));
            $wishlistId = $request->get('wishlist_id');
            $response = $wishlistService->getMemberWishlist($this->getUser(), $getMembers, $wishlistId);

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "",
                $response,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[get_member_wishlist_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }
}

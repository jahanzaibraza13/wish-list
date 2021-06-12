<?php

namespace App\ApiBundle\Controller\V1;

use App\ApiBundle\Enum\CommonEnum;
use App\ApiBundle\Service\UserService;
use App\ApiBundle\Service\UtilService;
use App\ApiBundle\Service\WishlistService;
use App\Entity\Wishlist;
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

            $data['code'] = $utilService->getRandomAlphaNumeric(CommonEnum::WISHLIST_CODE_LENGTH);


            $wishlist = $wishlistService->create($this->getUser(), $data);
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
     * @Route(methods={"POST"}, path="/user/wishlist/user", name="create_wishlist_user_api")
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
     *          in="formData",
     *          type="integer",
     *          required=false,
     *          description="Wishlist id"
     *      ),
     *      @SWG\Parameter(
     *          name="code",
     *          in="formData",
     *          type="string",
     *          required=false,
     *          description="Code"
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
     * @param Request $request
     * @param WishlistService $wishlistService
     * @param UserService $userService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createWishlistUserAction(
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

            if (empty($data['wishlist_id']) && empty($data['code'])) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Wishlist id or code is required."
                );
            }

            if (!empty($data['wishlist_id'])) {
                $wishlist = $wishlistService->getById($data['wishlist_id']);
            } else {
                $wishlist = $wishlistService->getByCode($data['code']);
            }

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

            $response = $wishlistService->addWishlistUser($wishlist, $user, $this->getUser());
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
     * @Route(methods={"POST"}, path="/user/wishlist/{wishlist_id}/user/remove", name="remove_wishlist_user_api")
     *
     * @Operation(
     *     tags={"Wishlist"},
     *     summary="Remove wish list user",
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
     *          required=false,
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
    public function removeWishlistUserAction(
        $wishlist_id,
        Request $request,
        WishlistService $wishlistService,
        UserService $userService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            $data = $request->request->all();
            $targetUser = null;

            /** @var Wishlist $wishlist */
            $wishlist = $wishlistService->getById($wishlist_id);
            if (empty($wishlist)) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Wishlist not found."
                );
            }

            if (!empty($data['user_id']) && $wishlist->getUser() != $this->getUser()) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Wishlist doesn't belong to the logged in user."
                );
            }

            if (!empty($data['user_id'])) {
                $targetUser = $userService->getUserById($data['user_id']);
                if (empty($targetUser)) {
                    return $utilService->makeResponse(
                        Response::HTTP_BAD_REQUEST,
                        "User not found."
                    );
                }
            }

            $response = $wishlistService->removeWishlistUser($wishlist, $this->getUser(), $targetUser);
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
            $userLogger->error('[remove_wishlist_user_api]: ' . $exception->getMessage());
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

    /**
     * @Route(methods={"DELETE"}, path="/user/wishlist/{wishlist_id}", name="delete_wishlist_api")
     *
     * @Operation(
     *     tags={"Wishlist"},
     *     summary="Delete wishlist",
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
     *     @SWG\Parameter(
     *          name="wishlist_id",
     *          in="path",
     *          type="integer",
     *          required=true,
     *          description="Wishlist id"
     *      ),
     * )
     *
     * @param $wishlist_id
     * @param Request $request
     * @param WishlistService $wishlistService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction(
        $wishlist_id,
        WishlistService $wishlistService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            /** @var Wishlist $wishlist */
            $wishlist = $wishlistService->getById($wishlist_id);

            if (empty($wishlist) || $wishlist->getUser() != $this->getUser()) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Wishlist not found."
                );
            }

            $wishlistService->deleteWishlist($wishlist);

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "Action performed successfully.",
                null,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[delete_wishlist_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    /**
     * @Route(methods={"GET"}, path="/user/wishlist/{wishlist_id}/generate-code", name="generate_wishlist_code_api")
     *
     * @Operation(
     *     tags={"Wishlist"},
     *     summary="Generate wishlist code",
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
     *      )
     * )
     *
     * @param Request $request
     * @param UtilService $utilService
     * @param WishlistService $wishlistService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function generateWishlistCodeAction(
        $wishlist_id,
        UtilService $utilService,
        WishlistService $wishlistService,
        LoggerInterface $userLogger
    ) {
        try {
            /** @var Wishlist $wishlist */
            $wishlist = $wishlistService->getById($wishlist_id);

            if (empty($wishlist) || $wishlist->getUser() != $this->getUser()) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Wishlist not found."
                );
            }

            $code = $utilService->getRandomAlphaNumeric(CommonEnum::WISHLIST_CODE_LENGTH);
            $wishlist->setCode($code);
            $this->getDoctrine()->getManager()->flush();

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "",
                ['code' => $code],
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[generate_wishlist_code_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }
}

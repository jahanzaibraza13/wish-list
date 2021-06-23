<?php

namespace App\ApiBundle\Controller\V1;

use App\ApiBundle\Enum\CommonEnum;
use App\ApiBundle\Service\UserService;
use App\ApiBundle\Service\UtilService;
use FOS\UserBundle\Model\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;

/**
 * Class UserController
 * @package App\ApiBundle\Controller\V1
 */
class UserController extends AbstractController
{
    /**
     * @Route(methods={"GET"}, path="/user/user", name="get_all_user_api")
     *
     * @Operation(
     *     tags={"User"},
     *     summary="Get all users",
     *     @SWG\Parameter(
     *       type="string",
     *       name="Authorization",
     *       in="header",
     *       required=true,
     *       description="Bearer your_token, Use user token here"
     *     ),
     *     @SWG\Parameter(
     *          name="page",
     *          in="query",
     *          type="integer",
     *          required=false,
     *          description="Page number"
     *      ),
     *      @SWG\Parameter(
     *          name="per_page",
     *          in="query",
     *          type="integer",
     *          required=false,
     *          description="Records per page. Default 50"
     *      ),
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
     *      )
     * )
     *
     * @param Request $request
     * @param UserService $userService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAllUserAction(
        Request $request,
        UserService $userService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            $data = $request->query->all();
            if (!empty($data['per_page']) && $data['per_page'] > CommonEnum::PER_PAGE_MAX) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "per_page page can not exceed the limit " . CommonEnum::PER_PAGE_MAX . "."
                );
            }

            $result = $userService->getAllUsers($data);

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "",
                $result,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[get_user_friend_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    /**
     * @Route(methods={"GET"}, path="/user/friend", name="get_user_friend_api")
     *
     * @Operation(
     *     tags={"User"},
     *     summary="Get current user details",
     *     @SWG\Parameter(
     *       type="string",
     *       name="Authorization",
     *       in="header",
     *       required=true,
     *       description="Bearer your_token, Use user token here"
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
     *      )
     * )
     *
     * @param Request $request
     * @param UserService $userService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getFriendAction(
        UserService $userService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            $result = $userService->getUserFriendList($this->getUser());

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "",
                $result,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[get_user_friend_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    /**
     * @Route(methods={"GET"}, path="/user/user-details", name="get_current_user_api")
     *
     * @Operation(
     *     tags={"User"},
     *     summary="Get current user details",
     *     @SWG\Parameter(
     *       type="string",
     *       name="Authorization",
     *       in="header",
     *       required=true,
     *       description="Bearer your_token, Use user token here"
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
     *      )
     * )
     *
     * @param UserService $userService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAction(
        UserService $userService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            $result = $userService->makeUserDetail($this->getUser());

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "",
                $result,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[get_user_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }

    /**
     * @Route(methods={"POST"}, path="/client/register", name="register_user_api")
     *
     * @Operation(
     *     tags={"User"},
     *     summary="Register user",
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
     *          name="first_name",
     *          in="formData",
     *          type="string",
     *          required=false,
     *          description="First name"
     *      ),
     *      @SWG\Parameter(
     *          name="last_name",
     *          in="formData",
     *          type="string",
     *          required=false,
     *          description="Last name"
     *      ),
     *      @SWG\Parameter(
     *          name="username",
     *          in="formData",
     *          type="string",
     *          required=true,
     *          description="Username"
     *      ),
     *     @SWG\Parameter(
     *          name="email",
     *          in="formData",
     *          type="string",
     *          required=true,
     *          description="Email"
     *      ),
     *     @SWG\Parameter(
     *          name="password",
     *          in="formData",
     *          type="string",
     *          required=true,
     *          description="Password"
     *      )
     * )
     *
     * @param Request $request
     * @param UserService $userService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function registerAction(
        Request $request,
        UserService $userService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            $data = $request->request->all();
            if (empty($data['username']) || empty($data['email'] || empty($data['password']))) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Missing required parameters."
                );
            }

            if ($utilService->isValidEmail($data['email'], "Invalid email provided.") !== true) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Invalid email provided."
                );
            }

            if (!empty($userService->getUserByEmail($data['email']))) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Email is already in use."
                );
            }

            if (!empty($userService->getUserByUsername($data['username']))) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Username is already in use."
                );
            }

            $userDetail = $userService->createUser($data);
            return $utilService->makeResponse(
                Response::HTTP_OK,
                "User registered successfully.",
                $userDetail,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[register_user_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }

    /**
     * @Route(methods={"POST"}, path="/user/add-friend", name="add_friend_api")
     *
     * @Operation(
     *     tags={"User"},
     *     summary="Add user friend",
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
     *          name="user_id",
     *          in="formData",
     *          type="integer",
     *          required=true,
     *          description="User id"
     *      ),
     *      @SWG\Parameter(
     *          name="accept_request",
     *          in="formData",
     *          type="integer",
     *          required=false,
     *          enum={0,1}
     *      ),
     * )
     *
     * @param Request $request
     * @param UserService $userService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function makeFriendAction(
        Request $request,
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
            $targetUser = $userService->getUserById($data['user_id']);

            if (empty($targetUser)) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "User not found."
                );
            }

            if ($targetUser == $this->getUser()) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Two same users can't be friends."
                );
            }

            if (!empty($data['accept_request'])) {
                $response = $userService->acceptRequest($this->getUser(), $targetUser);
            } else {
                $response = $userService->createUserFriend($this->getUser(), $targetUser);
            }

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
            $userLogger->error('[add_friend_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }

    /**
     * @Route(methods={"POST"}, path="/user/remove-friend", name="remove_friend_api")
     *
     * @Operation(
     *     tags={"User"},
     *     summary="Remove user friend",
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
     *          name="user_id",
     *          in="formData",
     *          type="integer",
     *          required=true,
     *          description="User id"
     *      ),
     * )
     *
     * @param Request $request
     * @param UserService $userService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeFriendAction(
        Request $request,
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
            $targetUser = $userService->getUserById($data['user_id']);

            if (empty($targetUser)) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "User not found."
                );
            }

            $response = $userService->removeUserFriend($this->getUser(), $targetUser);

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
            $userLogger->error('[add_friend_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }

    /**
     * @Route(methods={"POST"}, path="/client/forget-password", name="forget_password_api")
     *
     * @Operation(
     *     tags={"User"},
     *     summary="Forget password",
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
     *          name="email",
     *          in="formData",
     *          type="string",
     *          required=true,
     *          description="User email"
     *      )
     * )
     *
     * @param Request $request
     * @param UserService $userService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function forgetPasswordAction(
        Request $request,
        UserService $userService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            $data = $request->request->all();
            if (empty($data['email'])) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Email is required."
                );
            }
            $user = $userService->getUserByEmail($data['email']);

            if (empty($user)) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "User not found."
                );
            }

            $userService->sendNewPasswordEmail($user);
            return $utilService->makeResponse(
                Response::HTTP_OK,
                "Email with the new password has been sent to the user.",
                [],
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[forget_password_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    /**
     * @Route(methods={"PUT"}, path="/user/update-user", name="update_user_api")
     *
     * @Operation(
     *     tags={"User"},
     *     summary="Update user",
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
     *          name="password",
     *          in="formData",
     *          type="string",
     *          required=false,
     *          description="Password"
     *      ),
     *      @SWG\Parameter(
     *          name="username",
     *          in="formData",
     *          type="string",
     *          required=false,
     *          description="username"
     *      ),
     *      @SWG\Parameter(
     *          name="first_name",
     *          in="formData",
     *          type="string",
     *          required=false,
     *          description="First name"
     *      ),
     *      @SWG\Parameter(
     *          name="last_name",
     *          in="formData",
     *          type="string",
     *          required=false,
     *          description="Last name"
     *      ),
     * )
     *
     * @param Request $request
     * @param UserService $userService
     * @param UserManager $userManager
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateUserdAction(
        Request $request,
        UserService $userService,
        UserManager $userManager,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            $user = $this->getUser();
            $data = $request->request->all();
            if (isset($data['username']) && !empty($data['username'])) {
                $userExist = $userService->getUserByUsername($data['username']);
                if ($userExist && $user != $userExist) {
                    return $utilService->makeResponse(
                        Response::HTTP_BAD_REQUEST,
                        "User already exists with this username."
                    );
                }

                $user->setUsername($data['username']);
            }

            isset($data['password']) && !empty($data['password']) ? $user->setPlainPassword($data['password']) : null;
            isset($data['first_name']) && !empty($data['first_name']) ? $user->setFirstName($data['first_name']) : null;
            isset($data['last_name']) && !empty($data['last_name']) ? $user->setLastName($data['last_name']) : null;

            $userManager->updateUser($user);

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "User updated successfully.",
                [],
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[update_user_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    /**
     * @Route(methods={"DELETE"}, path="/user/user", name="delete_user_api")
     *
     * @Operation(
     *     tags={"User"},
     *     summary="Delete users",
     *     @SWG\Parameter(
     *       type="string",
     *       name="Authorization",
     *       in="header",
     *       required=true,
     *       description="Bearer your_token, Use user token here"
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
     *      )
     * )
     *
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteUserAction(
        UtilService $utilService,
        LoggerInterface $userLogger
    ): \Symfony\Component\HttpFoundation\JsonResponse
    {
        try {
            $this->getDoctrine()->getManager()->remove($this->getUser());
            $this->getDoctrine()->getManager()->flush();

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "User deleted successfully.",
                [],
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[delete_friend_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }
}

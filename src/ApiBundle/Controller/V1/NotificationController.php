<?php

namespace App\ApiBundle\Controller\V1;

use App\ApiBundle\Enum\CommonEnum;
use App\ApiBundle\Service\NotificationService;
use App\ApiBundle\Service\UtilService;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;

/**
 * Class NotificationController
 * @package App\ApiBundle\Controller\V1
 */
class NotificationController extends AbstractController
{
    /**
     * @Route(methods={"GET"}, path="/user/notification", name="get_notification_api")
     *
     * @Operation(
     *     tags={"Notification"},
     *     summary="Get notifications",
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
     * )
     *
     * @param Request $request
     * @param NotificationService $notificationService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAction(
        Request $request,
        NotificationService $notificationService,
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

            $notifications = $notificationService->getNotifications($this->getUser(), $data);

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "",
                $notifications,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[get_notification_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }

    /**
     * @Route(methods={"DELETE"}, path="/user/notification/{notification_id}", name="delete_notification_api")
     *
     * @Operation(
     *     tags={"Notification"},
     *     summary="Delete notifications",
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
     *          name="notification_id",
     *          in="path",
     *          type="integer",
     *          required=true,
     *          description="Notification id"
     *      ),
     * )
     *
     * @param $notification_id
     * @param Request $request
     * @param NotificationService $notificationService
     * @param UtilService $utilService
     * @param LoggerInterface $userLogger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction(
        $notification_id,
        Request $request,
        NotificationService $notificationService,
        UtilService $utilService,
        LoggerInterface $userLogger
    ) {
        try {
            /** @var Notification $notification */
            $notification = $notificationService->getById($notification_id);

            if (empty($notification) || $notification->getUser() != $this->getUser()) {
                return $utilService->makeResponse(
                    Response::HTTP_BAD_REQUEST,
                    "Notification not found."
                );
            }

            $notificationService->deleteNotification($notification);

            return $utilService->makeResponse(
                Response::HTTP_OK,
                "Action performed successfully.",
                null,
                CommonEnum::SUCCESS_RESPONSE_TYPE
            );
        } catch (\Exception $exception) {
            $userLogger->error('[delete_notification_api]: ' . $exception->getMessage());
            return $utilService->makeResponse(Response::HTTP_INTERNAL_SERVER_ERROR, CommonEnum::INTERNAL_SERVER_ERROR_TEXT);
        }
    }
}

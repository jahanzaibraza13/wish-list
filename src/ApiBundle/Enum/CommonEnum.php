<?php

namespace App\ApiBundle\Enum;

/**
 * Class CommonEnum
 * @package App\ApiBundle\Enum
 */
class CommonEnum
{
    const SUCCESS_RESPONSE_TYPE = 'success';
    const ERROR_RESPONSE_TYPE = 'error';

    const ROLE_APP_USER = 'ROLE_APP_USER';

    const INTERNAL_SERVER_ERROR_TEXT = "Something went wrong.";

    const PER_PAGE_MAX = 50;

    const NOTIFICATION_TYPE_REMOVE_FRIEND = "remove_friend";
    const NOTIFICATION_TYPE_ADD_FRIEND = "add_friend";
    const NOTIFICATION_TYPE_ADD_TO_WISHLIST = "add_to_wishlist";

    const WISHLIST_CODE_LENGTH = 5;

    const ITEM_LOGO_DIR = "/item_images";
}

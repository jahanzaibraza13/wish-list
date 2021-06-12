<?php

namespace App\ApiBundle\Service;

use App\ApiBundle\Enum\CommonEnum;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\RecursiveValidator as Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

/**
 * Class UtilService
 * @package App\ApiBundle\Service
 */
class UtilService
{
    const PUBLIC_DIR = "/public/";

    /** @var string */
    private $publicDir;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Validator
     */
    private $validator;
    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * UtilService constructor.
     * @param TranslatorInterface $translator
     * @param ValidatorInterface $validator
     * @param ParameterBagInterface $params
     */
    public function __construct(TranslatorInterface $translator, ValidatorInterface $validator, ParameterBagInterface $params)
    {
        $this->translator = $translator;
        $this->validator = $validator;
        $this->publicDir = $params->get('kernel.project_dir') . self::PUBLIC_DIR;
        $this->params = $params;
    }

    /**
     * @param $parameter
     * @return mixed
     */
    public function getParameter($parameter)
    {
        return $this->params->get($parameter);
    }

    /**
     * @param $statusCode
     * @param null $message
     * @param null $data
     * @param $type
     * @return JsonResponse
     */
    public function makeResponse($statusCode, $message = null, $data = null, $type = CommonEnum::ERROR_RESPONSE_TYPE)
    {
        $response['data'] = is_array($data) ? $data : null;

        $response['code'] = $statusCode;

        $response['message'] = !empty($message) ? $this->translator->trans($message) :
            CommonEnum::SUCCESS_RESPONSE_TYPE;

        $response['status'] = $type;

        return new JsonResponse($response, $statusCode);
    }

    /**
     * @param array $data
     * @param array $fields
     * @return array|bool
     */
    public function checkRequiredFieldsByRequestedData($data, array $fields)
    {
        $valuesArray = [];

        foreach ($fields as $field) {
            $value = isset($data[$field]) ? $data[$field] : null;

            if (empty($value)) {
                return false;
            }

            $valuesArray[$field] = $value;
        }

        return $valuesArray;
    }

    /**
     * @param $email
     * @param $message
     * @return bool|string
     */
    public function isValidEmail($email, $message)
    {
        $emailConstraint = new EmailConstraint();
        $emailConstraint->message = $message;

        $errorList = $this->validator->validate($email, $emailConstraint);

        if (count($errorList)) {
            return $errorList[0]->getMessage();
        }

        return true;
    }

    /**
     * @param $length
     * @return string
     * @throws \Exception
     */
    public function getRandomAlphaNumeric($length): string
    {
        $code = random_bytes($length);
        return bin2hex($code);
    }
}

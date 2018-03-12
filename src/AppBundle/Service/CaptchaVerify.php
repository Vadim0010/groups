<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CaptchaVerify
{
    const reCaptcha_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RequestStack
     */
    private $request;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    public function __construct(RequestStack $request, ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $request->getCurrentRequest();
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Проверить reCaptcha на валидность
     *
     * @return array
     */
    public function reCaptchaVerify()
    {
        $reCaptchaData = $this->request->get('g-recaptcha-response');
        $responseData = [
            'success' => false,
            'clientIp' => $this->request->getClientIp(),
            'error' => 'failed to verify reCaptcha'
        ];

        if ( empty($reCaptchaData) || ! isset($reCaptchaData) ) {
            $responseData['error'] = 'reCaptcha not found';
            return $responseData;
        }

        $responseData['response'] = $this->getReCaptchaResponse($reCaptchaData);

        if ( !$this->accessor->getValue($responseData, '[response][success]') ) {
            $responseData['error'] = 'defined as a robot';
            return $responseData;
        }

        $responseData['success'] = true;
        return $responseData;
    }

    /**
     * @param $reCaptcha
     * @return mixed
     */
    private function getReCaptchaResponse($reCaptcha)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => static::reCaptcha_URL,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POSTFIELDS => [
                'secret' => $this->container->getParameter('reCaptche_secret_key'),
                'response' => $reCaptcha,
                'remoteip' => $this->request->getClientIp()
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
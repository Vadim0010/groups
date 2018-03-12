<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Config\Definition\Exception\Exception;
use GuzzleHttp\Client;

class InstagramApiClient
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var Client
     */
    private $guzzleClient;

    private  $accessor;

    const GROUP_ID = "/(?:(?:http|https):\/\/)?(?:www.)?(?:instagram.com|instagr.am)\/([A-Za-z0-9-_.]+)/im";

    public function __construct(RequestStack $request, SessionInterface $session)
    {
        $this->request = $request->getCurrentRequest();
        $this->session = $session;
        $this->guzzleClient = new Client();
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function parse($url=null)
    {
        if($data = $this->isValidUrl($url)){
            $instagramId = $this->getInstagramId($data);
            $url = $this->createUrl($instagramId);

            return $this->grabData($url);
        }

        throw new Exception('Unable to parse group');
    }

    /**
     * @return []
     */
    protected function isValidUrl($url=null)
    {
        if(is_null($url)) {
            $url = $this->request->get('link');
        }

        preg_match(static::GROUP_ID, $url, $matches);

        return $matches;
    }

    /**
     * @param $data array
     *
     * @return mixed
     */
    protected function getInstagramId($data)
    {
        $data = $this->accessor->getValue($data, '[1]');

        if(is_null($data)){
            throw new Exception('Instagram data is empty');
        }

        return $data;
    }

    /**
     * @param $instagramId
     *
     * @return string
     */
    protected function createUrl($instagramId)
    {
        // https://www.instagram.com/uladzimir_kosak/
        // https://www.instagram.com/alla.sergeeva.92/
        return sprintf(
            'https://instagram.com/%s?__a=1',
            $instagramId
        );
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    protected function grabData($url)
    {
        $data = $this->guzzleClient->request('GET', $url);
        $data = \GuzzleHttp\json_decode($data->getBody(), true);

        $data = $this->normalize($data);

        $data['status'] = 200;
        $data['errors'] = false;

        $this->session->set('group_info', $data);

        return $data;
    }

    private function normalize($data)
    {
        $tmp = [];
        $tmp['subscribers'] = $this->accessor->getValue($data, '[user][followed_by][count]');
        $tmp['name'] = $this->accessor->getValue($data, '[user][full_name]');
        if(! $tmp['name']){
            $tmp['name'] = $this->accessor->getValue($data, '[user][username]');
        }
        $tmp['group_avatar'] = $this->accessor->getValue($data, '[user][profile_pic_url_hd]');
        $tmp['preview'] = $this->accessor->getValue($data, '[user][profile_pic_url]');
        $tmp['social'] = 'in';

        return $tmp;
    }

    public function checkCode($url, $code)
    {
        if(! preg_match(static::GROUP_ID, $url, $m)){
            return false;
        }
        $instagramId = $this->accessor->getValue($m, '[1]');
        $url = $this->createUrl($instagramId);
        $response = $this->guzzleClient->request('GET', $url);
        $data = \GuzzleHttp\json_decode($response->getBody(), true);

        return $this->accessor->getValue($data, '[user][biography]') === $code;
    }
}
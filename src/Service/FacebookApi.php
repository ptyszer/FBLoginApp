<?php
namespace App\Service;

use Facebook\Facebook;

class FacebookApi
{
    private $appId;
    private $secret;
    private $fbSdk;
    private $permissions=array();
    private $callback;
    private $userData;

    /**
     * @param string $appId
     * @param string $secret
     */
    public function __construct($appId, $secret)
    {
        $this->appId = $appId;
        $this->secret = $secret;
        $this->fbSdk=new Facebook(array('app_id' => $this->appId, 'app_secret' => $this->secret));
    }

    /**
     * @param array $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @param string $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    public function login($request) {
        $helper = $this->fbSdk->getRedirectLoginHelper();

        if ($request->query->get('state') != null) {
            $helper->getPersistentDataHandler()->set('state', $request->query->get('state'));
        }

        try {
            $accessToken = $helper->getAccessToken();// to fetch access token
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (!isset($accessToken))// checks whether access token is in there or not
        {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }
        try {
            $response = $this->fbSdk->get('/me?fields=id,name,email,birthday,picture.type(large),first_name,last_name,friends,gender,location', $accessToken); // to get required fields using access token
        } catch (\Facebook\Exceptions\FacebookResponseException $e)// throws an error if invalid fields are specified
        {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $this->userData = $response->getGraphUser();// to get user details
        return true;
    }

    public function getLoginUrl() {
        $helper = $this->fbSdk->getRedirectLoginHelper();
        return $helper->getLoginUrl($this->callback, $this->permissions);
    }

    public function getUserData() {
        return $this->userData;
    }

}
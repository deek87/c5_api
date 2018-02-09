<?php


namespace C5JapanAPI\Credential;


use Concrete\Core\User\UserInfo;

class UserCredential
{

    protected $token;
    protected $userInfo;
    protected $userSecret;
    protected $clientID;

    /**
     * @return mixed
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * @param mixed $clientID
     */
    public function setClientID($clientID)
    {
        $this->clientID = $clientID;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return UserInfo
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @param UserInfo $userInfo
     */
    public function setUserInfo($userInfo)
    {
        $this->userInfo = $userInfo;
    }

    /**
     * @return mixed
     */
    public function getUserSecret()
    {
        return $this->userSecret;
    }

    /**
     * @param mixed $userSecret
     */
    public function setUserSecret($userSecret)
    {
        $this->userSecret = $userSecret;
    }

    /**
     * UserCredential constructor.
     * @param $userInfo UserInfo
     * @param $userSecret string
     * @param $clientID string
     * @param $token string
     */
    public function __construct(UserInfo $userInfo, $userSecret, $clientID)
    {
        $this->setUserInfo($userInfo);
        $this->setUserSecret($userSecret);
        $this->setClientID($clientID);

    }



}
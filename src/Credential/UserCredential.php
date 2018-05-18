<?php


namespace C5JapanAPI\Credential;


use Concrete\Core\User\UserInfo;

/**
 * Class UserCredential
 * @package C5JapanAPI\Credential
 */
class UserCredential
{

    /** @var string */
    protected $token;
    /** @var UserInfo */
    protected $userInfo;
    /** @var string */
    protected $userSecret;
    /** @var string */
    protected $clientID;

    /**
     * @return string
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * @param $clientID string
     */
    public function setClientID($clientID)
    {
        $this->clientID = $clientID;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $token string
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
     * @param $userInfo UserInfo
     */
    public function setUserInfo(UserInfo $userInfo)
    {
        $this->userInfo = $userInfo;
    }

    /**
     * @return string
     */
    public function getUserSecret()
    {
        return $this->userSecret;
    }

    /**
     * @param $userSecret string
     */
    public function setUserSecret($userSecret)
    {
        $this->userSecret = $userSecret;
    }

    /**
     * @return string
     */
    public function getSecret() {
        return $this->getUserSecret();
    }

    /**
     * UserCredential constructor.
     * @param UserInfo $userInfo
     * @param string $userSecret
     * @param string $clientID
     */
    public function __construct(UserInfo $userInfo, $userSecret, $clientID)
    {
        $this->setUserInfo($userInfo);
        $this->setUserSecret($userSecret);
        $this->setClientID($clientID);

    }



}
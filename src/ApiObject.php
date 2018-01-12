<?php


namespace C5JapanAPI;


/**
 * Class ApiObject
 */
class ApiObject
{
    /** @var array */
    protected $requestArray = [];
    /** @var null|int */
    protected $expires = null;
    /** @var null|string */
    protected $token = null;
    /** @var null|string */
    protected $clientID = null;
    /** @var null|string */
    protected $secret = null;
    /** @var int */
    protected $userID = 0;
    /** @var bool */
    protected $hasToken = true;

    /**
     * ApiObject constructor.
     * @param $requestArray array
     */
    public function __construct($requestArray)
    {
        if (is_array($requestArray)) {
            $this->requestArray = $requestArray;
            $succesfulExtraction = $this->extractInformation();
            if ($succesfulExtraction !== true) {
                throw new \Exception(t('Missing %s field from request', $succesfulExtraction));
            }
        } else {
            return t('Invalid Request Array');
        }

    }

    /**
     * @return bool|string
     */
    protected function extractInformation()
    {
        if (isset($this->requestArray['expires'])) {
            $this->expires = $this->requestArray['expires'];
        }

        if (isset($this->requestArray['client_id'])) {
            $this->clientID = $this->requestArray['client_id'];
        } else {
            return 'client_id';
        }

        if (isset($this->requestArray['client_secret'])) {
            $this->secret = $this->requestArray['client_secret'];
        } else {
            return 'client_secret';
        }

        if (isset($this->requestArray['token'])) {
            $this->token = $this->requestArray['token'];
        }

        if (isset($this->requestArray['user_id'])) {
            $this->userID = $this->requestArray['user_id'];
        } else {
            return 'user_id';
        }

        return true;
    }

    /**
     * @return null|int
     */
    public function getExpireTime()
    {
        return $this->expires;
    }

    /**
     * @return string
     */
    public function getSecret() {
        return $this->secret;
    }

    /**
     * @param bool $state
     */
    public function tokenRequired($state = true)
    {
        if (is_bool($state)) {
                $this->hasToken = $state;
        } else {
            $this->hasToken = true;
        }

    }

    /**
     * @param null|string $field
     * @return bool|mixed
     */
    public function getExtraField($field = 'nothing')
    {
        if (isset($this->requestArray[$field])){
            return $this->requestArray[$field];
        } else {
            return false;
        }
    }

    /**
     * @param int|null $expires
     */
    public function setExpireTime($expires)
    {
        $this->expires = $expires;
    }

    /**
     * @param null|string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @param null|string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @param null|string $clientID
     */
    public function setClientID($clientID)
    {
        $this->clientID = $clientID;
    }

    /**
     * @return string
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getUserID()
    {
        return $this->userID;
    }


    /**
     * @return bool
     */
    public function hasToken()
    {
        return $this->hasToken;
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: derekcameron
 * Date: 21/02/2018
 * Time: 12:18
 */

namespace C5JapanAPI\Request;


use C5JapanAPI\Model\AbstractModel;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Http\Request;
use Concrete\Core\Support\Facade\Facade;
use Concrete\Core\User\UserInfo;
use Concrete\Core\User\UserInfoRepository;

/**
 * Class AbstractRequest
 * @package C5JapanAPI\Request
 */
abstract class AbstractRequest extends AbstractModel implements RequestInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;
    /** @var null|int */
    protected $expires = null;
    /** @var null|string */
    protected $token = null;
    /** @var null|string */
    protected $clientID = null;
    /** @var null|string */
    protected $secret = null;
    /** @var UserInfo */
    protected $user = 0;
    /** @var bool */
    protected $hasToken = true;
    /** @var string */
    protected $requestPath = '';
    /** @var Request */
    protected $request;
    /** @var array  */
    protected $data = [];

    public function __construct(Request $request)
    {
        $this->request= $request;
        $this->requestPath = str_replace('ccm/api/v1.0/', '', $request->getPath());
        $extracted = $this->extractInfo();
        if ($extracted !== true) {
            throw new \Exception(t('Missing %s field from request', $extracted));
        } else {
            return t('Invalid Request Array');
        }
        $this->convertData();

    }

    /** Sets a custom user by UserInfo object
     *
     * @param UserInfo $userInfo
     */
    public function setCustomUser(UserInfo $userInfo)
    {
        $this->request->setCustomRequestUser($userInfo);
    }

    /** Function call on construct to extract vital information such as client_id/client_secret/etc
     * @return bool|string
     */
    final private function extractInfo()
    {
        if ($this->request->getMethod() == 'GET') {
            $requestArray = $this->request->query->all();
        } else {
            $requestArray = $this->request->request->all();
        }

        if (isset($requestArray['client_id'])) {
            $this->clientID = $requestArray['client_id'];
            unset($requestArray['client_id']);
        } else {
            return 'client_id';
        }

        if (isset($requestArray['client_secret'])) {
            $this->secret = $requestArray['client_secret'];
            unset($requestArray['client_secret']);
        } else {
            return 'client_secret';
        }

        if (isset($requestArray['user_id'])) {

            if (isset($this->app)) {
                $userInfo = $this->app->make(UserInfoRepository::class)->getByID($requestArray['user_id']);
            } else {
                $this->app = Facade::getFacadeApplication();
                $userInfo = $this->app->make(UserInfoRepository::class)->getByID($requestArray['user_id']);
            }
                    if ($userInfo instanceof UserInfo) {
                        $this->user = $userInfo;
                    } else {
                        throw new \Exception(t('Invalid User ID'));
                    }
            unset($requestArray['user_id']);
            // Incase this is set we wont to remove it;
            unset($requestArray['user']);
        } else {
            return 'user_id';
        }
        $this->mapData($requestArray);
        unset($requestArray);

        return true;

    }

    /**
     * @return int|null
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param int|null $expires
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param null|string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return null|string
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * @param null|string $clientID
     */
    public function setClientID($clientID)
    {
        $this->clientID = $clientID;
    }

    /**
     * @return null|string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param null|string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @return UserInfo
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInfo $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function isHasToken()
    {
        return $this->hasToken;
    }

    /**
     * @param bool $hasToken
     */
    public function setHasToken($hasToken)
    {
        $this->hasToken = $hasToken;
    }

    /**
     * @return string
     */
    public function getRequestPath()
    {
        return $this->requestPath;
    }

    /**
     * @param string $requestPath
     */
    public function setRequestPath($requestPath)
    {
        $this->requestPath = $requestPath;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @see $this->getRequestPath();
     * @deprecated
     * @return string
     */
    public function getPath()
    {
        return $this->getRequestPath();
    }


}
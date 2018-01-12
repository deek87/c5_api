<?php
/**
 * Created by PhpStorm.
 * User: derekcameron
 * Date: 22/12/2017
 * Time: 13:15
 */

namespace C5JapanAPI;


use Concrete\Core\Application\Application;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Package\PackageService;
use Concrete\Core\User\UserInfoRepository;

/**
 * Class CredentialGenerator
 */
class CredentialGenerator implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;
    /** @var  \Concrete\Core\User\UserInfo $userID */
    protected $userInfo;

    /**
     * CredentialGenerator constructor.
     * @param int $userID
     */
    public function __construct(Application $app, $userID = 1)
    {
        $this->setApplication($app);

            /** @var UserInfoRepository $userInfoRepository */
            $this->userInfo = $this->app->make(UserInfoRepository::class)->getByID($userID);

    }

    /**
     * @return \Concrete\Core\User\UserInfo
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * CredentialGenerator destructor.
     *
     */
    public function __destruct()
    {
        unset($this->userInfo);
        return false;
    }

    /**
     * @return int
     */
    public function getUserID()
    {
        return $this->userInfo->getUserID();
    }

    /**
     * @param int $userID
     */
    public function setUser($userID)
    {
        $this->userInfo =  $this->app->make(UserInfoRepository::class)->getByID($userID);
    }

    /**
     * @return string
     */
    public function generateClientID()
    {

        $part1 = $this->userInfo->getUserDateAdded()->format('Ymd');
        $part2 = $this->userInfo->getUserEmail();
        $part3 = $this->userInfo->getUserName();
        $clientID = hash('sha256',$part1 . "-" .$part2 . "-" .$part3);

        $clientID = bin2hex($clientID);
        if (strlen($clientID) > 64) {
            $clientID = substr($clientID, 0, 64);
        }
        return $clientID.'-C5:api';
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        /** @var \Concrete\Core\Package\Package $package */
        $package = $this->app->make(PackageService::class)->getByHandle('concrete5_japan_api');
        $packageConfig = $package->getConfig();
        $secret = $packageConfig->get('api.secret.'.$this->getUserID());
        if (empty($secret)){
            $secret = $this->generateSecret();
        }

        return $secret;
    }

    /**
     * @return array
     */
    public function generateToken()
    {
        $expires = time() + 360;
        $token = $this->app->make('token')->generate($this->generateClientID() .'-_-' . $expires);

        return ['expires'=>$expires, 'token'=>$token];
    }

    /**
     * @param int $expires
     * @return bool
     */
    public function validateToken($expires = 0)
    {
        if ($this->app->make('token')->validate($this->generateClientID() . '-_-'. $expires)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $length
     * @return string
     */
    private function getRandomBytes($length = 16)
    {
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            return openssl_random_pseudo_bytes($length);
        }

        if (@is_readable('/dev/urandom') &&
            ($fh = @fopen('/dev/urandom', 'rb'))) {
            $output = fread($fh, $length);
            fclose($fh);

            if (strlen($output) === $length) {
                return $output;
            }
        }

        if (function_exists('mcrypt_create_iv')) {
            $mcryptCheck = @mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            if ($mcryptCheck != false) {
                return $mcryptCheck;
            }

        }

        if (function_exists('mcrypt_create_iv')) {
            $mcryptCheck = mcrypt_create_iv($length, MCRYPT_RAND);
            if ($mcryptCheck != false) {
                return $mcryptCheck;
            }
        }



        $randomBytes = '';
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+{}|":<>?\'\\';
        $characterLength = strlen($characters);
        while ($length--) {
            $randomBytes .= substr($characters, rand(0, $characterLength), 1);
        }

        return $randomBytes;
    }

    /**
     * @return string
     */
    public function generateSecret()
    {

        $email = $this->userInfo->getUserEmail();

        $random = $this->getRandomBytes(16);
        $random2 = $this->getRandomBytes(16);

        $secret = bin2hex(hash('sha256', $random2 .'!-!' . $email . '!-!' . $random));
        /** @var \Concrete\Core\Package\Package $package */
        $package = $this->app->make(PackageService::class)->getByHandle('concrete5_japan_api');
        $packageConfig = $package->getConfig();
        $packageConfig->save('api.secret.'.$this->getUserID(), $secret);

        return $secret;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: derekcameron
 * Date: 09/02/2018
 * Time: 18:03
 */

namespace C5JapanAPI\Credential;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\Application;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\User\UserInfo;
use Concrete\Core\Package\PackageService;

class CredentialService implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    public function __construct(Application $app)
    {
        $this->setApplication($app);
    }

    /**
     * @param UserInfo $userInfo
     * @return string
     */
    public function generateClientID(UserInfo $userInfo) {

            $part1 = $userInfo->getUserDateAdded()->format('Ymd');
            $part2 = $userInfo->getUserEmail();
            $part3 = $userInfo->getUserName();
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
    public function getSecret(UserInfo $userInfo)
    {
        /** @var \Concrete\Core\Package\Package $package */
        $package = $this->app->make(PackageService::class)->getByHandle('concrete5_japan_api');
        $packageConfig = $package->getConfig();
        $secret = $packageConfig->get('api.secret.'.$userInfo->getUserID());
        if (empty($secret)){
            $secret = $this->generateSecret($userInfo);
        }

        return $secret;
    }


    /**
     * @param UserInfo $userInfo
     * @return string
     */
    public function getClientID(UserInfo $userInfo)
    {
        return $this->generateClientID($userInfo);
    }

    /**
     * Function used to generate secret from a UserInfo Object
     *
     * @param UserInfo $userInfo
     * @return string
     */
    public function generateSecret(UserInfo $userInfo)
    {

        $email = $userInfo->getUserEmail();

        $random = $this->getRandomBytes(16);
        $random2 = $this->getRandomBytes(16);

        $secret = bin2hex(hash('sha256', $random2 .'!-!' . $email . '!-!' . $random));
        /** @var \Concrete\Core\Package\Package $package */
        $package = $this->app->make(PackageService::class)->getByHandle('concrete5_japan_api');
        $packageConfig = $package->getConfig();
        $packageConfig->save('api.secret.'.$userInfo->getUserID(), $secret);

        return $secret;
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

}
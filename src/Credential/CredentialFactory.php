<?php
/**
 * Created by PhpStorm.
 * User: derekcameron
 * Date: 09/02/2018
 * Time: 17:49
 */

namespace C5JapanAPI\Credential;



use Concrete\Core\Support\Facade\Facade;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;
use Concrete\Core\User\UserInfoRepository;

/**
 * Factory used to get credentials all methods are static
 *
 * Class CredentialFactory
 *
 * @package C5JapanAPI\Credential
 */
class CredentialFactory
{
    /**
     *  Alias for function getByUserID
     *
     * @param $userID
     * @return UserCredential|string
     */
    public static function getByID($userID)
    {
        return self::getByUserID($userID);
    }

    /**
     * Gets a UserCredential by user id
     *
     * @param $userID
     * @return UserCredential|string
     */
    public static function getByUserID($userID)
    {
        $app = Facade::getFacadeApplication();
        $userInfo = $app->make(UserInfoRepository::class)->getByID($userID);
        if ($userInfo instanceof UserInfo) {
            return self::getByUserInfo($userInfo);
        } else {
            return t('Invalid User ID');
        }

    }

    /**
     * Gets a UserCredential by User Object
     *
     * @param User $user
     * @return UserCredential
     */
    public static function getByUser(User $user)
    {
        return self::getByUserInfo($user->getUserInfoObject());
    }

    /**
     * Gets a UserCredential by User Info Object
     *
     * @param UserInfo $userInfo
     * @return UserCredential
     */
    public static function getByUserInfo(UserInfo $userInfo)
    {
        $service = self::getService();
        $secret = $service->getSecret($userInfo);
        $clientID =$service->generateClientID($userInfo);
        return new UserCredential($userInfo, $secret, $clientID);
    }

    /**
     * Function used to create a credential service
     *
     * @return CredentialService
     */
    public static function getService() {

        $app = Facade::getFacadeApplication();
        return $app->make(CredentialService::class);

    }



}
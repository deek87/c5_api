<?php


namespace C5JapanAPI\Methods;

use Concrete\Core\Permission\Checker;
use Concrete\Core\User\UserInfoRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Permission\Checker as PermissionChecker;


/**
 * Class PostMethod
 */
class PostMethod extends AbstractMethod
{

    /**
     * @param null|string $params
     * @return JsonResponse
     */
    public function update($params = null)
    {
        if ($this->validate()){

            if ($params == null) {
                return new JsonResponse(['message'=>t('Unable to process request')], 410);
            }

            $params = explode('/',$params);
            if ($params[0] === 'page'){
                return $this->updatePage($params[1]);
            }

            if($params[0] === 'user') {
                return $this->updateUser($params[1]);
            }

            return new JsonResponse(['message'=>t('Error processing request')], 410);
        } else {
            return new JsonResponse(['message'=>t('Unable to validate')], 401);
        }

    }

    /**
     * @param null|string $params
     * @return JsonResponse
     */
    public function delete($params = null)
    {
        if ($this->validate()){

            if ($params == null) {
                return new JsonResponse(['message'=>t('Unable to process request')], 410);
            }

            $params = explode('/',$params);
            if ($params[0] === 'page'){
                return $this->deletePage($params[1]);
            }

            if($params[0] === 'user') {
                return $this->deleteUser($params[1]);
            }

            return new JsonResponse(['message'=>t('Error processing request')], 410);
        } else {
            return new JsonResponse(['message'=>t('Unable to validate')], 401);
        }

    }

    protected function deleteUser($userID) {
        if ($this->canAccess($userID, 'delete_user')){
            /** @var \Concrete\Core\User\UserInfo $userInfo */
            $userInfo = $this->app->make(UserInfoRepository::class)->getByID($userID);

            if ($userInfo->triggerDelete($this->credentialGenerator->getUserInfo()->getUserObject())) {
                return new JsonResponse(['message'=>t('User deleted')]);
            } else {
                return new JsonResponse(['message'=>t('Workflow submitted')]);
            }

        } else {
            return new JsonResponse(['message'=>t('Access Denied')],401);
        }
    }

    /**
     * @param null|int $userID
     * @return JsonResponse
     */
    protected function updateUser($userID = null)
    {
        if ($this->canAccess($userID, 'edit_user')) {

        } else {
            return new JsonResponse(['message'=>t('Access Denied')], 401);
        }

    }

    /**
     * @param $typeID
     * @param $type
     * @return bool
     */
    private function canAccess($typeID, $type)
    {
        $this->request->setCustomRequestUser($this->credentialGenerator->getUserInfo());

        if (strpos($type, 'user')){
            /** @var \Concrete\Core\User\UserInfo $userInfo */
            $userInfo = $this->app->make(UserInfoRepository::class)->getByID($typeID);
            /** @var \Concrete\Core\Permission\Response\UserInfoResponse $userPermissions */
            $userPermissions = new PermissionChecker($userInfo);
            if ($type == 'edit_user' && !$userPermissions->canEditUser()) {
                echo 'nope';
                return false;
            }
            if ($type == 'delete_user') {
                $taskPermissions = new Checker();

                return $taskPermissions->canDeleteUser();
            }
        }

        $requesterInfo = $this->credentialGenerator->getUserInfo();

        if ($requesterInfo->getUserObject()->isSuperUser() || $type == 'edit_user' && $typeID == $requesterInfo->getUserID()){
            return true;
        }
    }

}
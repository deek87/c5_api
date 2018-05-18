<?php


namespace C5JapanAPI\Method;

use C5JapanAPI\Commands\CreatePageCommand;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Type;
use Concrete\Core\Permission\Checker;
use Concrete\Core\User\UserInfoRepository;
use Concrete\Core\Validation\SanitizeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Permission\Checker as PermissionChecker;


/**
 * Class PostMethod
 */
class PostMethod extends AbstractMethod
{

    public function create($params = null) {
        if ($this->validate()){

            if ($params == null) {
                return new JsonResponse(['message'=>t('Unable to process request')], 410);
            }

            $params = explode('/',$params);
            if ($params[0] === 'page'){
                $this->createPage($params[1]);
                return $this->sendRequest();

            }

            if($params[0] === 'user') {
                return new JsonResponse($this->createUser($params[1]));
            }

            return new JsonResponse(['message'=>t('Error processing request')], 410);
        } else {
            $params = explode('/',$params);
            if ($params[0] === 'page'){
                $this->createPage($params[1]);
                return new JsonResponse($this->sendRequest());

            }
            return new JsonResponse(['message'=>t('Unable to validate')], 401);
        }
    }

    protected function createPage($pageID = 0)
    {
        if ($pageID == null || $pageID == 0) {
            $pageID = $this->getApiObject()->getExtraField('parent');

        }
        $parentPage = Page::getByID($pageID);
        if (!is_object($parentPage) || !$parentPage->isError()) {
            $parentPage = null;
        }
        $dataArray = $this->getApiObject()->getExtraField('data');
        if (!is_array($dataArray)){
            $dataArray = [];
        }
        $publishDate = date_create($this->getApiObject()->getExtraField('publishDate'));
        if ($publishDate == false) {
            $publishDate = new \DateTime();
        }

        $pageTypeID = $this->app->make(SanitizeService::class)->SanitizeInt($this->getApiObject()->getExtraField('pageType'));
        $pageTypeHandle = $this->app->make(SanitizeService::class)->SanitizeString($this->getApiObject()->getExtraField('pageType'));

        //Try ID first
        $pageType = Type::getByID($pageTypeID);
        if (!is_object($pageType) || $pageType->isError()) {
            // If it fails then try the handle
            $pageType = Type::getByHandle($pageTypeHandle);
        }
        if (!is_object($pageType) || $pageType->isError()) {
            // If both fail then set to null Command will take care of it
            $pageType = null;
        }

        $pageTemplateID = $this->app->make(SanitizeService::class)->SanitizeInt($this->getApiObject()->getExtraField('pageType'));
        $pageTemplateHandle = $this->app->make(SanitizeService::class)->SanitizeString($this->getApiObject()->getExtraField('pageType'));

        //Try ID first
        $pageTemplate = Type::getByID($pageTemplateID);
        if (!is_object($pageType) || $pageTemplate->isError()) {
            // If it fails then try the handle
            $pageTemplate = Type::getByHandle($pageTemplateHandle);
        }
        if (!is_object($pageTemplate) || $pageTemplate->isError()) {
            // If both fail then set to null Command will take care of it
            $pageTemplate = null;
        }
        $this->request->setCustomRequestUser($this->credentialGenerator->getUserInfo());
        $this->setCommand(new CreatePageCommand(
            $this->getApiObject()->getExtraField('name'),
            $this->getApiObject()->getExtraField('description'),
            $parentPage,
            $dataArray,
            $publishDate,
            $pageType,
            $pageTemplate));

    }

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
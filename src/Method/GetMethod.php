<?php


namespace C5JapanAPI\Method;

use Concrete\Core\Page\Page;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;
use Concrete\Core\User\UserInfoRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Package\PackageService;


class GetMethod extends AbstractMethod
{

    /**
     *  function for testing purposes only to be removed later
     *
     * @return JsonResponse
     */
    public function adminDetails()
    {


        $userObject = $this->app->make(User::class);
        if ($userObject->isSuperUser()){
            $userID = $this->apiObject->getExtraField('user');
            $this->credentialGenerator->setUser($userID);

            $details = [
                'client_secret'=> $this->credentialGenerator->getSecret(),
                'client_id'=>$this->credentialGenerator->generateClientID(),
                'user_id'=>$userID
                ];

            $status = 200;
        } else {
            $status = 401;
            $details = ['message'=>t('Unable to verify user')];
        }

        return new JsonResponse($details, $status);
    }

    /**
     * @param null $params
     * @return JsonResponse
     */
    public function details($params = null)
    {

        if ($this->validate()){

            if ($params == null) {
                return new JsonResponse(['message'=>t('Unable to process request')], 410);
            }

            $params = explode('/',$params);
            if ($params[0] === 'page'){
                return $this->getPageDetails($params[1]);
            }

            if($params[0] === 'user') {
                return $this->getUserDetails($params[1]);
            }

            return new JsonResponse(['message'=>t('Error processing request')], 410);
        } else {
            return new JsonResponse(['message'=>t('Unable to validate')], 401);
        }

    }

    /** @return JsonResponse */
    public function token()
    {
        $this->apiObject->tokenRequired(false);
        if ($this->validate()){
            $tokenArray = $this->credentialGenerator->generateToken();
            $details = [
                'client_secret'=> $this->credentialGenerator->getSecret(),
                'client_id'=>$this->credentialGenerator->generateClientID(),
                'user_id'=>$this->apiObject->getUserID(),
                'token'=>$tokenArray['token'],
                'expires'=>$tokenArray['expires']
            ];
            return new JsonResponse($details, 200);

        }else {
            return new JsonResponse(['message'=>t('Unable to validate')], 401);
        }
    }

    /**
     * @param int $cID
     * @return JsonResponse
     */
    protected function getPageDetails($cID = 0)
    {
        $status = 200;
        $pageObject = Page::getByID($cID);
        if (is_object($pageObject)) {
            $blockArray = [];
            $blocks = $pageObject->getBlocks();
            /** @var \Concrete\Core\Block\Block $block */
            foreach ($blocks as $block) {
                $blockArray[] = [
                    'blockID'=>$block->getBlockID(),
                    'blockHandle' => $block->getBlockTypeHandle(),
                    'displayOrder' => $block->getBlockDisplayOrder(),
                    'lastUpdated' => $block->getBlockDateLastModified(),
                    'isAlias'=> $block->isAlias($pageObject),
                    'isActive'=> $block->isActive()
                ];
            }
            $details= [
                'pageName'=>$pageObject->getCollectionName(),
                'pageDescription'=>$pageObject->getCollectionDescription(),
                'pageUrl'=> $pageObject->getCollectionLink(true),
                'pageAuthorID'=> $pageObject->getCollectionUserID(),
                'pageAuthor'=> $this->app->make(UserInfoRepository::class)->getByID($pageObject->getCollectionUserID())->getUserName(),
                'pageID' => $cID,
                'active' => $pageObject->isActive(),
                'publicDate' => $pageObject->getCollectionDatePublic(),
                'children'=> $pageObject->getNumChildren(),
                'blocks' => $blockArray
            ];
        } else {
            $details = [
                'message'=>t('Page Not Found')
            ];
            $status = 404;
        }

            return new JsonResponse($details, $status);
    }

    /**
     * @param int $userID
     * @return JsonResponse
     */
    protected function getUserDetails($userID = 0) {
        $status = 200;
        /** @var UserInfo $userObject */
        $userObject = $this->app->make(UserInfoRepository::class)->getByID($userID);
        if (is_object($userObject)) {

            $details = [
                'userName' => $userObject->getUserName(),
                'userID'=> $userObject->getUserID(),
                'userEmail' => $userObject->getUserEmail(),
                'lastOnline'=> $userObject->getLastOnline(),
                'profileLink'=> $userObject->getUserPublicProfileUrl()->__toString(),
                'profilePicture'=> $userObject->getUserAvatar()->getPath(),
            ];

            $details = $this->getAdditionalUserDetails($userObject, $details);

        }else {
            $details = [
                'message'=>t('User Not Found')
            ];
            $status = 404;
        }

        return new JsonResponse($details, $status);

    }

    /**
     * @param UserInfo $userInfo
     * @param array $details
     * @return array
     */
    protected function getAdditionalUserDetails(UserInfo $userInfo, $details) {
        /** @var \Concrete\Core\Package\Package $package */
        $package = $this->app->make(PackageService::class)->getByHandle('concrete5_japan_api');
        $packageConfig = $package->getConfig();
        $userDetails = $packageConfig->get('api.user.details');
        if (is_array($userDetails) && !empty($userDetails)) {
            foreach ($userDetails as $userDetail) {
                $attribute = $userInfo->getAttributeValueObject($userDetail, false);
                if (!empty($attribute)) {
                    $details[$userDetail] = $attribute->getPlainTextValue();
                } else {
                    $details[$userDetail] = '';
                }

            }

        }

        return $details;
    }

}
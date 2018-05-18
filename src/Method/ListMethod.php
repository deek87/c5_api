<?php


namespace C5JapanAPI\Method;


use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\PageList;
use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Concrete\Core\User\UserList;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\User\UserInfoRepository;
use Concrete\Core\Application\Application;
use C5JapanAPI\ApiObject;


/**
 * Class RequestList
 */
class ListMethod extends AbstractMethod
{
    /** @var \Concrete\Core\Validation\SanitizeService sanitizeService */
    protected $sanitizeService;


    /**
     * ListMethod constructor.
     * @param Application $app
     * @param ApiObject $apiObject
     */
    public function __construct(Application $app, ApiObject $apiObject)
    {
        parent::__construct($app, $apiObject);
        
        $this->sanitizeService = $this->app->make('helper/security');
    
    }

    /**
     * @return JsonResponse
     */
    public function pages()
    {
        /** @var PageList $pageList */
        $pageList = $this->app->make(PageList::class);
        $authorID = $this->getField('author_id');
        if (!empty($authorID)) {
            $pageList->filterByUserID($authorID);
        }
        $blockTypeHandle = $this->getField('block_type_handle', 'string');
        if (!empty($blockTypeHandle)) {
            $blockType = BlockType::getByHandle($blockTypeHandle);
            if (is_object($blockType)) {
                $pageList->filterByBlockType($blockType);
            }
        }
        $this->getOffsetLimit($pageList);

        $parentPage = $this->getField('parent_page');

        if (!empty($parentPage)) {
            $pageList->filterByParentID($parentPage);
        }

        $results = $pageList->getResults();
        $details = ['results'=>count($results), 'query'=>$pageList->getQueryObject()->getSQL()];
        /** @var Page $pageObject */
        foreach ($results as $pageObject) {
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
            $details[$pageObject->getCollectionID()]= [
                'pageName'=>$pageObject->getCollectionName(),
                'pageDescription'=>$pageObject->getCollectionDescription(),
                'pageUrl'=> $pageObject->getCollectionLink(true),
                'pageAuthorID'=> $pageObject->getCollectionUserID(),
                'pageAuthor'=> $this->app->make(UserInfoRepository::class)->getByID($pageObject->getCollectionUserID())->getUserName(),
                'pageID' => $pageObject->getCollectionID(),
                'active' => $pageObject->isActive(),
                'publicDate' => $pageObject->getCollectionDatePublic(),
                'children'=> $pageObject->getNumChildren(),
                'blocks' => $blockArray
            ];
        }

        return new JsonResponse($details, 200);

    }

    /**
     * Gets a field from the apiObject based upon $fieldname and sanitizes it based upon $fieldType
     *
     * Options for $fieldType are 'int','url','email','string' - leaving blank will default to int
     *
     * @param $fieldName
     * @param string $fieldType
     * @return bool|mixed
     */
    protected function getField($fieldName, $fieldType = 'int')
    {
        
        $field = $this->apiObject->getExtraField($fieldName);
        if ($fieldType == 'int') {
            $field = $this->sanitizeService->sanitizeInt($field);
        } elseif ($fieldType == 'url') {
            $field = $this->sanitizeService->sanitizeURL($field);
        } elseif ($fieldType == 'email') {
            $field = $this->sanitizeService->sanitizeEmail($field);
        } else {
            $field = $this->sanitizeService->sanitizeString($field);
        }
        
        return $field;
        
    }

    /**
     * @return JsonResponse
     */
    public function users()
    {
        /** @var UserList $userList */
        $userList = $this->app->make(UserList::class);
        $groupName = $this->getField('groupName', 'string');
        if (!empty($groupName)) {
            $userList->filterByGroup($groupName);    
        }
        $groupID = $this->getField('groupID');
        
        if (!empty($groupID)) {
            $userList->filterByGroupID($groupID);
        }
        
        $noGroup = $this->apiObject->getExtraField('noGroup');
        if ($noGroup == true) {
            $userList->filterByNoGroup();
        }
        $keywords = $this->getField('keywords','string');
        if (!empty($keywords)) {
            $userList->filterByKeywords($keywords);    
        }
        $this->getOffsetLimit($userList);

        $userName = $this->getField('userName', 'string');
        if (!empty($userName)) {
            $userList->filterByFuzzyUserName($userName);
        }

        $isActive = $this->getField('isActive', 'int');

        if ($isActive !== '' && ($isActive == 0 | $isActive == 1)) {
            $userList->filterByIsActive($isActive);
        }
        $isValidated = $this->getField('isValid', 'int');
        if ($isValidated !== '' && ($isValidated == 0 | $isValidated == 1)) {
            $userList->filterByIsValidated($isValidated);
        }



$details = [];
        $results = $userList->getResults();
        /** @var \Concrete\Core\User\UserInfo $userObject */
        foreach ($results as $userObject) {
            $details[$userObject->getUserID()] = [
                'userName' => $userObject->getUserName(),
                'userID'=> $userObject->getUserID(),
                'userEmail' => $userObject->getUserEmail(),
                'lastOnline'=> $userObject->getLastOnline(),
                'profileLink'=> $userObject->getUserPublicProfileUrl()->__toString(),
                'profilePicture'=> $userObject->getUserAvatar()->getPath(),
            ];

        }

        if(count($results) == 0) {
            return new JsonResponse(['message'=>t('No Users Found')], 200);
        }

        return new JsonResponse($details, 200);

    }

    /**
     * Function to set the Offset and Limit of an attributed item list
     *
     * @param AttributedItemList $list
     */
    private function getOffsetLimit(AttributedItemList &$list)
    {
        $limit = $this->getField('page_limit');
        if (!empty($limit)) {
            $list->getQueryObject()->setMaxResults($limit);
        } else {
            $list->getQueryObject()->setMaxResults(50);
        }

        $offset = $this->getField('page_offset');
        if (!empty($offset)) {
            $list->getQueryObject()->setFirstResult($offset);
        }
    }

}

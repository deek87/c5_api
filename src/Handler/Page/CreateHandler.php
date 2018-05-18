<?php


namespace C5JapanAPI\Handler\Page;

defined('C5_EXECUTE') or die("Access Denied.");

use C5JapanAPI\Command\Page\CreateCommand;
use Concrete\Core\Entity\Page\Template;
use Concrete\Core\Validation\SanitizeService;
use Concrete\Core\Page\Template as TemplateType;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Type;
use Concrete\Core\Page\Type\Composer\Control\Control;
use Concrete\Core\User\UserInfo;
use Concrete\Core\User\User;
use C5JapanAPI\Handler\AbstractCommandHandler;

/**
 * Class CreatePageHandler
 * @package C5JapanAPI\Handler
 */
class CreateHandler extends AbstractCommandHandler
{
    /** @var CreateCommand $command */
    protected $command;


    public function handle() {

        $this->checkPageRequiredInfo();

        if (is_object($this->command->getParent()) && !$this->command->getParent()->isError()) {
            /** @var Page $pageDraft */
            $pageDraft = $this->command->getParent()->add($this->command->getPageType(), $this->command->getData(), $this->command->getPageTemplate());
        } else {
            $pageDraft = null;
        }
        if (is_object($pageDraft) && !$pageDraft->isError()) {
            $pageDraft->setPageDraftTargetParentPageID($this->command->getParent()->getCollectionID());
            if (is_object($pageDraft->getPageTypeObject())) {
                $controlList = Control::getList($pageDraft->getPageTypeObject());
                foreach ($controlList as $control) {

                    $control->onPageDraftCreate($pageDraft);
                    $control->publishToPage($pageDraft, $this->command->getData(), $controlList);
                }
                $pageDraft->getPageTypeObject()->publish($pageDraft, $this->command->getPublishDate());
                // We need to get the most recent version as $pageDraft is currently the pageDraft version.
                $pageDraft = Page::getByID($pageDraft->getCollectionID(), 'RECENT');
            }
        } else {
            $pageDraft = null;
        }
            $this->command->setReturnObject($pageDraft);

    }

    /**
     * @param $data array
     * @return mixed
     */
    protected function parseData($data)
    {

        if (is_array($data)) {
            $data = array_merge($data, ['cvIsApproved' => 0, 'cIsDraft' => 1, 'cIsActive' => false, 'cAcquireComposerOutputControls' => true]);
        } else {
            $data = ['cvIsApproved' => 0, 'cIsDraft' => 1, 'cIsActive' => false, 'cAcquireComposerOutputControls' => true];
        }

        if (!isset($data['cName'])){
            $data['cName'] = $this->command->getPageName();
        }
        if (!isset($data['cDescription'])) {
            $data['cDescription'] = $this->command->getPageDescription();
        }
        if(!isset($data['cHandle'])){
            $data['cHandle'] = $this->command->getOption('pageHandle');
        }
        $data['uID'] = $this->command->getOption('uID');

        $this->command->setData($data);


    }

    public function getRequestData()
    {
        /** @var SanitizeService $sanitizer */
        $sanitizer = $this->app->make(SanitizeService::class);
        $options = [];
        $options['pageName'] = $this->request->get('name') ?: $options['pageName'];
        /** @var $user User */
        $user = $this->app->make(User::class);
        $this->command->setUser($user->getUserInfoObject());
        $options['pageDescription'] = $this->request->get('description') ?: $options['pageDescription'];
        $options['pageHandle'] = $sanitizer->sanitizeURL($this->request->get('page_handle'))?: $options['pageHandle'];
        $options['publishDate'] = $this->request->get('publish_date');
        $this->validateTemplate($this->request->get('page_template'));
        $this->validatePageType($this->request->get('page_type'));
        $pageID = $this->request->get('parent');
        $this->command->setOptions($options);

        $this->command->setParent(Page::getByID($pageID));
        if (!is_object($this->command->getParent()) || !$this->command->getParent()->isError()) {
            $this->command->setParent(null);
        }

        $this->command->setPublishDate($this->validateDate($this->request->get('publish_date')));
        $this->data = $this->parseData($this->request->request->get('data'));
    }

    protected function parseOptions()
    {
        $options = $this->command->getOptions();
        if ($options['user'] instanceof UserInfo ) {
            $this->command->setUserID($options['user']->getUserID());
        } else {

        }
        unset($options['user']);
        if ($options['parent'] instanceof Page) {
            $this->command->setParent($options['parent']);
        }
        unset($options['parent']);
        if ($options['parentPage'] instanceof Page) {
            $this->command->setParent($options['parent']);
        }
        unset($options['parentPage']);
        if (isset($options['pageType'])) {
            if ($options['pageType'] instanceof Type) {
                $this->command->setPageType($options['pageType']);
            } else {
                $this->validatePageType($options['pageType']);
            }
            unset($options['pageType']);
        }
        if (isset($options['pageTemplate'])) {
            if ($options['pageTemplate'] instanceof Template) {
                $this->command->setPageTemplate($options['pageTemplate']);
            } else {
                $this->validateTemplate($options['pageTemplate']);
            }
            unset($options['pageTemplate']);
        }

        if (is_array($options['data'])) {
            $data = $options['data'];
            unset($options['data']);
            $data = array_merge($options, $data);
            $this->parseData($data);
        } else {
            $this->parseData($options);
        }
    }

    /**
     * @param $publishDate string
     * @return null|\DateTime
     */
    protected function validateDate($publishDate)
    {
        if (is_null($publishDate) || empty($publishDate)) {
            return date_create();
        }

        $date = date_create($publishDate);
        if ($date) {
            return $date;
        } else {
            return date_create();
        }
    }

    /**
     * @param null $pageType
     */
    protected function validatePageType($pageType = null) {
        /** @var SanitizeService $sanitizer */
        $sanitizer = $this->app->make(SanitizeService::class);
        $pageTypeID = $sanitizer->SanitizeInt($pageType);
        $pageTypeHandle = $sanitizer->SanitizeString($pageType);

        //Try ID first
        $pageType = Type::getByID($pageTypeID);
        if (!is_object($pageType)) {
            // If it fails then try the handle
            $pageType = Type::getByHandle($pageTypeHandle);
        }
        if (!is_object($pageType)) {
            // If both fail then set to null
            $pageType = null;
        }
        $this->command->setPageType($pageType);
    }

    protected function validateTemplate($pageTemplate = null) {
        /** @var SanitizeService $sanitizer */
        $sanitizer = $this->app->make(SanitizeService::class);

        $pageTemplateID = $sanitizer->SanitizeInt($pageTemplate);
        $pageTemplateHandle = $sanitizer->SanitizeString($pageTemplate);

        //Try ID first
        $pageTemplate = TemplateType::getByID($pageTemplateID);
        if (!is_object($pageTemplate)) {
            // If it fails then try the handle
            $pageTemplate = TemplateType::getByHandle($pageTemplateHandle);
        }
        if (is_object($pageTemplate)) {
            $this->command->setPageTemplate($pageTemplate);
        }

    }

}
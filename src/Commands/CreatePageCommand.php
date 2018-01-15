<?php


namespace C5JapanAPI\Commands;

use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Type;
use Concrete\Core\User\User;
use Concrete\Core\Page\Type\Composer\Control\Control;


class CreatePageCommand extends AbstractCommand
{

    /** @var \Concrete\Core\Entity\Page\Template $pageTemplate */
    protected $pageTemplate = null;
    /** @var \Concrete\Core\Page\Type\Type|null  $pageType */
    protected $pageType = null;
    /** @var Page|null $parent */
    protected $parent = null;
    /** @var string|null */
    protected $publishDate = null;

    /**
     * CreatePageCommand constructor.
     * @param string $pageName
     * @param string $pageDescription
     * @param Page|null $parent
     * @param array $data
     * @param null $pageTemplate
     */
    public function __construct($pageName, $pageDescription = '', Page $parent = null, $data = [], \DateTime $publishDate = null, $pageType = null ,  $pageTemplate = null)
    {

        parent::__construct($this->validateData($data));

        $this->pageName = $pageName;
        $this->pageDescription = '';

        $this->data['cName'] =  $pageName;

        $this->data['cDescription']= $pageDescription;

        $this->data['uID'] = $this->app->make(User::class)->getUserID();
        if (is_object($parent)) {
            $this->parent = $parent;
        } else {
            $this->parent= $this->app->make('site')->getSite()->getSiteHomePageObject();
        }

        if (!is_object($this->parent) || $this->parent->isError()) {
            $this->parent = Page::getByID(HOME_CID);
        }

        if (is_object($pageType)) {

            $this->pageType = $pageType;
            if ($parent == null) {
                $this->parent = $this->pageType->getPageTypePublishTargetObject();
            }
        } else {
            $this->pageType = $this->parent->getPageTypeObject();
            if (!is_object($this->pageType)) {
                $this->pageType = Type::getByHandle('page');
            }

        }
        if (is_object($pageTemplate)) {
            $this->pageTemplate = $pageTemplate;
        } else {
            if (!is_object($this->pageType)) {
                $this->pageTemplate = $this->parent->getPageTemplateObject();

            } else {
                $this->pageTemplate = $this->pageType->getPageTypeDefaultPageTemplateObject();
            }


        }

        if (is_object($publishDate) && $publishDate instanceof \DateTime) {
            $this->publishDate = $publishDate->format('Y-m-d H:i:s');
        } else {
            $this->publishDate = $this->validateDate($publishDate);
        }



    }

    protected function validateDate($publishDate) {
        if (is_object($publishDate) || is_null($publishDate)) {
            return null;
        }

        $date = date_create($publishDate);
        if ($date) {
            return $date->format('Y-m-d H:i:s');
        } else {
            return null;
        }
    }

    protected function validateData($data)
    {

        $this->data = array_merge($data, ['cvIsApproved' => 0, 'cIsDraft' => 1, 'cIsActive' => false, 'cAcquireComposerOutputControls' => true]);

        return $this->data;
    }

    public function execute()
    {
        /** @var Page $pageDraft */

        $pageDraft = $this->parent->add($this->pageType, $this->data, $this->pageTemplate);

        if (is_object($pageDraft) && !$pageDraft->isError()) {
            $pageDraft->setPageDraftTargetParentPageID($this->parent->getCollectionID());
            if (is_object($pageDraft->getPageTypeObject())) {
                $controlList = Control::getList($pageDraft->getPageTypeObject());
                foreach ($controlList as $control) {

                    $control->onPageDraftCreate($pageDraft);
                    $control->publishToPage($pageDraft, $this->data, $controlList);
                }

                if (!$this->publishDate instanceof \DateTime) {
                    $this->publishDate = date_create(time());
                }

                $pageDraft->getPageTypeObject()->publish($pageDraft, $this->publishDate);
                if ($pageDraft->isPageDraft()) {
                    return ['message'=>t('Page Submited to Workflow'), 'object'=>$pageDraft];
                } else {
                    return ['message'=>t('Page Added Successfully.'), 'object'=>$pageDraft];
                }
            } else {
                return ['message'=>t('Page Draft Created'), 'object'=>$pageDraft];
            }
        } else {
            return ['message'=>t('An error occured while adding this page.')];
        }


    }

}
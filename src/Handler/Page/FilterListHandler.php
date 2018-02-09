<?php


namespace C5JapanAPI\Handler\Page;
defined('C5_EXECUTE') or die("Access Denied.");
use C5JapanAPI\Command\Page\FilterListCommand;
use C5JapanAPI\Handler\AbstractCommandHandler;
use Concrete\Core\Validation\SanitizeService;
use Concrete\Core\Page\Page;

class FilterListHandler extends AbstractCommandHandler
{
    /** @var $command FilterListCommand */
    protected $command;

    public function handle()
    {

       $this->getRequestData();

       // Do things for every one
    }


    protected function parseOptions()
    {
        if ($this->command->getOption('page')) {
            $this->command->setOption('parentPage', $this->command->getOption('page'));
        }
        if ($this->command->getOption('parent')) {
            $this->command->setOption('parentPage', $this->command->getOption('parent'));
        }

        if (is_array($this->command->getOption('filters'))) {
            foreach ($this->command->getOption('filters') as $filter) {
                $this->determineFilters($filter);
            }
        }



    }

    /**
     * Function used to determine which filters to use
     *
     * @param $filter
     */
    protected function determineFilters($filter)
    {
        switch ($filter){
            case ('keywords' || 'words' || 'text'):
                $this->command->getPageList()->filterByKeywords($this->command->getOption('keywords'));
                break;
            default;
            case ('parent' || 'page' || 'parentPage'):
                $this->getParentPage($this->command->getOption('parentPage'));
                break;
            case ('block' || 'blockType'):
                $this->command->getPageList()->filterByBlockType($this->command->getOption('blockType'));
                break;
        }
    }


    protected function getParentPage($string) {

        if ($string instanceof Page) {
            $this->command->getPageList()->filterByParentID($this->command->getOption('parentPage')->getCollectionID());
        } else {
            /** @var SanitizeService $sanitizer */
            $sanitizer = $this->app->make(SanitizeService::class);
            $page = Page::getByID($sanitizer->sanitizeInt($string));
            if (is_object($page) && !$page->isError()) {
                $this->command->getPageList()->filterByParentID($page->getCollectionID());
            }

            $page = Page::getByPath($sanitizer->sanitizeString($string));
            if (is_object($page) && !$page->isError()) {
                $this->command->getPageList()->filterByParentID($page->getCollectionID());
            }
        }
    }


}
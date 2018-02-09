<?php


namespace C5JapanAPI\Command\Page;
defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\API\Transformer\PageListTransformer;
use Concrete\Core\Page\PageList;
use C5JapanAPI\Command\AbstractCommand;

class FilterListCommand extends AbstractCommand
{
    /** @var  PageList $pageList */
    protected $pageList;

    protected $filters = [];

    public function __construct(PageList $pageList)
    {
        $this->pageList = $pageList;
    }

    /**
     * @return PageList
     */
    public function getPageList()
    {
        return $this->pageList;
    }

    /**
     * @param PageList $pageList
     */
    public function setPageList(PageList $pageList) {
        $this->pageList = $pageList;
    }

    public function setFilter($filterName, $filterValue) {
        $this->filters[] = [$filterName => $filterValue];
    }

    public function setFilters($filters = ['filterName'=>'filterValue']) {
        if (is_array($filters)) {
            $this->filters = $filters;
        } else {
            $this->filters = [$filters];
        }
    }

    public function getFilter($filterName) {
        return $this->filters[$filterName];
    }





}
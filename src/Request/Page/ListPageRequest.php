<?php
namespace C5JapanAPI\Request\Page;


use C5JapanAPI\Request\AbstractRequest;
use Concrete\Core\Page\PageList;

class ListPageRequest extends AbstractRequest
{
    protected $parent;

    protected $filters;
    protected $filtersType = 'array';
    protected $filtersClass = '\C5JapanAPI\Filter\PageFilter';

    public function convertData()
    {

    }

    public function getList() {
        $pageList = new PageList();
        /** @var \C5JapanAPI\Filter\PageFilter $filter */
        foreach ($this->filters as $filter) {
            $filter->filter($pageList);
        }
        return $pageList->getResults();
    }

}
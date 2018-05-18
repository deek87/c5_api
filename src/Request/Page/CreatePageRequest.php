<?php

namespace C5JapanAPI\Request\Page;


use C5JapanAPI\Request\AbstractRequest;

class CreatePageRequest extends AbstractRequest
{

    /** @var \Concrete\Core\Entity\Page\Template $pageTemplate */
    protected $pageTemplate = null;
    /** @var \Concrete\Core\Page\Type\Type|null $pageType */
    protected $pageType = null;
    /** @var Page|null $parent */
    protected $parent = null;
    /** @var string|null */
    protected $publishDate = null;
    /** @var $pageName string */
    protected $pageName = null;
    /** @var $data array */
    protected $data = [];

    public function convertData() {

    }

}
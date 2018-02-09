<?php


namespace C5JapanAPI\Command\Page;
defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Page\Page;
use C5JapanAPI\Command\AbstractCommand;


/**
 * Class used to create new pages via the command bus
 *
 * Class CreatePageCommand
 * @package C5JapanAPI
 */
class CreateCommand extends AbstractCommand
{

    /** @var \Concrete\Core\Entity\Page\Template $pageTemplate */
    protected $pageTemplate = null;
    /** @var \Concrete\Core\Page\Type\Type|null $pageType */
    protected $pageType = null;
    /** @var Page|null $parent */
    protected $parent = null;
    /** @var string|null */
    protected $publishDate = null;
    /** @var Page|null $parent */
    protected $returnObject = null;


}
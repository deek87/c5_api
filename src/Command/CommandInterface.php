<?php
namespace C5JapanAPI\Command;
defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Interface CommandInterface
 * @package C5JapanAPI
 */
interface CommandInterface
{

    /** @param array $data */
    public function setOptions($options = []);

    /** @param array $data */
    public function setData($data = []);

}

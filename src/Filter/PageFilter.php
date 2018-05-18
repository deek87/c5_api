<?php
/**
 * Created by PhpStorm.
 * User: derekcameron
 * Date: 23/02/2018
 * Time: 14:36
 */

namespace C5JapanAPI\Filter;

use Concrete\Core\Page\PageList;

class PageFilter extends AbstractFilter
{
    const FIELDS = [
        'keywords'=>['type'=>['string'], 'function'=>'filterByKeywords'],
        'parent'=>[['int','array'], 'function'=>'filterByParentID'],
        'username'=>['type'=>['string'],'function'=>'filterByTopic'],
        'page'=>['type'=>['int','array'],'function'=>'filterByParentID'],
        'topic'=>['type'=>['int'],'function'=>'filterByTopic'],
        'topics'=>['type'=>['int'],'function'=>'filterByTopic'],
        'user_id'=>['type'=>['int'],'function'=>'filterByUserID'],
    ];

    /**
     * @param &$list
     */
    public function filter(&$list)
    {
        if ($this->value !==  null) {


            if ($list instanceof PageList) {
                if (method_exists($list, $this->function)) {

                    $list->{$this->function}($this->value);
                } elseif (method_exists($list, 'filterBy'. ucfirst($this->camelCase($this->name)))) {

                    $list->{'filterBy'.ucfirst($this->camelCase($this->name))}($this->value);
                } elseif (method_exists($this,$list->{$this->function})) {

                  $this->{$this->function}($list);
                } else {

                    $list->filter($this->name,$this->value);
                }
            } else {
                throw new \Exception(t('Invalid List Passed'));
            }
        }
    }

}
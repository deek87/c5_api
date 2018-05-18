<?php

namespace C5JapanAPI\Request;

use Concrete\Core\User\UserInfo;

interface RequestInterface
{
    /** Function used to setCustomUser on Requests
     * @param UserInfo $userInfo
     */
    public function setCustomUser(UserInfo $userInfo);
    /** Function used to convert mapped data into Classes/etc such as parent/owner/etc */
    public function convertData();
    /** @return UserInfo */
    public function getUser();
    /** @return string */
    public function getToken();
    /** @return string */
    public function getSecret();
    /** @return string */
    public function getExpires();
    /** @return string */
    public function getClientID();
    /** @return string */
    public function getPath();


}
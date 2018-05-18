<?php
namespace C5JapanAPI\Request\Validator;

use C5JapanAPI\Request\RequestInterface;

interface ValidatorInterface
{
    /**
     * Function used to validate a request
     *
     * @param RequestInterface $request
     * @return null|\Exception;
     */
    public function validate(RequestInterface $request);

    /**
     * function used to checkCredentials of a request
     *
     * @param RequestInterface $request
     * @return null|\Exception;
     */
    public function checkCredentials(RequestInterface $request);

}

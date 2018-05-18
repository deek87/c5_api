<?php
/**
 * Created by PhpStorm.
 * User: derekcameron
 * Date: 21/02/2018
 * Time: 13:07
 */
namespace C5JapanAPI\Request\Validator;

use C5JapanAPI\Credential\CredentialFactory;
use C5JapanAPI\Request\RequestInterface;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Console\Application;
use Concrete\Core\Support\Facade\Facade;

abstract class AbstractValidator implements ValidatorInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /** @var \C5JapanAPI\Credential\UserCredential $userCredentials */
    protected $userCredentials;

    /**
     * Maybe add our own Exception?
     * @param RequestInterface $request
     * @throws \Exception
     * @return null|\Exception;
     */
    final public function checkCredentials(RequestInterface $request)
    {
        $this->userCredentials = CredentialFactory::getByUserInfo($request->getUser());
        if (empty($request->getToken()) && $request->getRequestPath() !== 'token') {
            throw new \Exception(t('No Token Supplied'));
        }
        if ($this->userCredentials->getUserSecret() !== $request->getSecret()) {
            throw new \Exception(t('Invalid Credentials'));
        }

        if ($this->userCredentials->getClientID() !== $request->getClientID()) {
            throw new \Exception(t('Invalid Credentials'));
        }
        if (!empty($request->getToken())) {
            $this->userCredentials->setToken($request->getToken());
            $this->validateToken($request->getExpires());
        }

    }

    /** Function to validateToken of request
     * @param int $expires
     * @throws \Exception
     */
    protected function validateToken($expires = 0) {
        if (time() > $expires) {
            throw new \Exception(t('Token Has Expired'));
        }
        if (!isset($this->app) || !$this->app instanceof Application) {
            $this->setApplication(Facade::getFacadeApplication());
        }
        if (!$this->app->make('token')->validate($this->userCredentials->getToken(), $this->userCredentials->getClientID() .'-_-' . $expires)) {
            throw new \Exception(t('Invalid Token'));
        }
    }

    public function __destruct()
    {
     unset($this->userCredentials);
     unset($this->app);
    }

}
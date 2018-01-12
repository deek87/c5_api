<?php
/**
 * Created by PhpStorm.
 * User: derekcameron
 * Date: 21/12/2017
 * Time: 18:30
 */

namespace C5JapanAPI\Methods;

use C5JapanAPI\ApiObject;
use C5JapanAPI\CredentialGenerator;
use Concrete\Core\Application\Application;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Class AbstractMethod
 */
abstract class AbstractMethod implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;
    protected $apiObject;
    /** @var $credentialGenerator CredentialGenerator */
    protected $credentialGenerator;
    /** @var Request */
    protected $request;
    /** @var  \C5JapanAPI\Commands\AbstractCommand */
    protected $command;

    /**
     * AbstractMethod constructor.
     * @param ApiObject $apiObject
     */
    public function __construct(Application $app, ApiObject $apiObject)
    {
        $this->apiObject = $apiObject;
        $this->setApplication($app);
        $this->credentialGenerator = $this->app->make(CredentialGenerator::class,['userID'=>$this->apiObject->getUserID()]);
        $this->request = Request::getInstance();
    }

    /**
     * @return bool
     */
    protected function validate()
    {


        if ($this->apiObject->hasToken()) {
            // Validate token
            if (!$this->validateToken()) {
                return false;
            }
            // Check if expired
            if ($this->apiObject->getExpireTime() < time()) {
                return false;
            }
        }
        // Validate Client ID
        if (!$this->validateClientID()) {
            echo 'oops';
            return false;
        }
        // Validate Secret ID
        if (!$this->validateSecret()) {
            echo 'hello';
            return false;
        }
        return true;
    }

    public function validateToken() {

        return $this->app->make('token')->validate($this->credentialGenerator->generateClientID() . '-_-' . $this->apiObject->getExpireTime(), $this->apiObject->getToken());


    }
    public function validateClientID(){

        if ($this->credentialGenerator->generateClientID() === $this->apiObject->getClientID()) {
            return true;
        } else {
            return false;
        }

    }
    public function validateSecret() {
        if ($this->credentialGenerator->getSecret() === $this->apiObject->getSecret()){
            return true;
        } else {
            return false;
        }
    }
    /**
     * @return ApiObject
     */
    public function getApiObject()
    {
        return $this->apiObject;
    }

    /**
     * @return mixed
     */
    public function sendRequest()
    {
        return $this->command->execute();
    }


}
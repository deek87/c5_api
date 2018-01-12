<?php


namespace C5JapanAPI;

use C5JapanAPI\Methods\ListMethod;
use Concrete\Core\Controller\Controller;
use C5JapanAPI\Methods\GetMethod;
use C5JapanAPI\Methods\PostMethod;
use Symfony\Component\HttpFoundation\JsonResponse;



class ApiController extends Controller
{

    public function getRoute($method, $action, $params = 0){
        if ($method == 'get') {
            $this->apiObject = $this->app->make(ApiObject::class, ['requestArray'=>$this->get()]);
            $getMethod = $this->app->make(GetMethod::class, ['apiObject'=> $this->apiObject]);

            if (method_exists($getMethod, $action)) {
                return $getMethod->$action($params);
            } else {
                echo $method;
                echo ' - ';
                echo $action;
                echo '-';
                echo $params;
                return new JsonResponse(t('Action Doesn\'t Exist!'), 404);
            }

        } elseif ($method == 'post') {
            $this->apiObject = $this->app->make(ApiObject::class, ['requestArray'=>$this->get()]);
            $postMethod = $this->app->make(PostMethod::class, ['apiObject'=> $this->apiObject]);
            if (method_exists($postMethod, $action)) {
                return $postMethod->$action($params);
            } else {
                return new JsonResponse(t('Action Doesn\'t Exist!'), 404);
            }
        } elseif ($method == 'list') {
            $this->apiObject = $this->app->make(ApiObject::class, ['requestArray'=>$this->get()]);
            $listMethod = $this->app->make(ListMethod::class, ['apiObject'=> $this->apiObject]);
            if (method_exists($listMethod, $action)) {
                return $listMethod->$action($params);
            } else {
                return new JsonResponse(t('Action Doesn\'t Exist!'), 404);
            }
        }else {
            return new JsonResponse(t('Method Doesn\'t Exist!'), 404);
        }
    }
}
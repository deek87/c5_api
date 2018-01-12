<?php
namespace Concrete\Package\Concrete5JapanApi;
defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single as SinglePage;
use Concrete\Core\Job\Job;




class Controller extends Package
{

    /**
     *
     * @author Derek Cameron <derek@concrete5.co.jp>
     *
     */

    protected $pkgHandle = 'concrete5_japan_api';
    protected $appVersionRequired = '8.2.1';
    protected $pkgVersion = '0.0.9';
    protected $pkgAutoloaderRegistries = array('src/' => '\C5JapanAPI');

    public function getPackageDescription()
    {
        return t('Add\'s a RESTful API to Concrete5');
    }

    public function getPackageName()
    {
        return t('Concrete5 RESTful API');
    }

    public function install()
    {

        $package = parent::install();
        // Install our job
        $this->checkJobs($package);
        $this->installPages($package, '/dashboard/system/api', ['cName'=>'RESTful API', 'cDescription'=>'RESTful API Settings']);
        $this->checkConfig($package);

    }

    public function upgrade()
    {
        $package = $this->getPackageEntity();

    }


    /** Function for checking if config variables exist and if not installing the defaults.
     *
     * @param $package Package
     *
     */
    private function checkConfig($package) {

        $config = $package->getConfig();
        $config->get('concrete.api.secret');

    }


    /** Function for updating or installing this package's jobs
     *
     * @param $package Package
     *
     */
    private function checkJobs($package)
    {
        // Check to see if any old Jobs from this package exist and remove them
        $jobs = Job::getListByPackage($package);
        foreach ($jobs as $job) {
            if (!is_object($job)) {
                $job->delete();
            }
        }

    }

    /** Function for installing or Updating Single Pages
     *
     * @param $package Package
     * @param $page string
     * @param $page array
     *
     */
    private function installPages($package, $page, $info)
    {
        // Check if the page exists
        $singlePage = SinglePage::add($page, $package);
        if (is_null($singlePage)) {
            $singlePage = Page::getByPath($page);
            if (is_object($singlePage) && !$singlePage->isError()) {
                // Update the information if it does exist
                $singlePage->update($info);
            }
        } else {
            $singlePage->update($info);

        }

    }

    public function on_start()
    {

        \Route::register('/api/{method}/{action}','\C5JapanAPI\ApiController::getRoute', 'api_entry_point',['method'=>'(get|post|list)']);;
        \Route::register('/api/{method}/{action}/{params}','\C5JapanAPI\ApiController::getRoute', 'api_entry_point_with_params',['method'=>'(get|post)','params'=>'(user|page)\/\d+'], ['params'=>'user/1']);

    }



}
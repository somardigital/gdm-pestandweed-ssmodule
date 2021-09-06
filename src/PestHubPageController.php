<?php 

namespace gurudigital\pesthub; 

use SilverStripe\Core\Config\Config;
use SilverStripe\View\Requirements;
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Director;

class PestHubPageController extends \PageController 
{
    /*
    * Get current url without query string
    *
    * @return string Url 
    */
    public function getUrl() {
        $url = Director::absoluteUrl($this->getRequest()->getUrl());
        if(substr($url, -1) != '/') {
            $url.="/";
        }
        return $url;
    }

    /*
    * Get HTML of either the list of pests/weeds or the individual pest/weed
    * depending on the query
    */
    public function getPestContent() {
        $url = $this->getUrl();
        $pestHub = null;
        try {
            $pestHub = PestHub::fromUrl($url);
        } catch (PestHubException $ex) {
            if ($ex->data) {    
                $pestHub = PestHub::fromData($ex->data);
                Injector::inst()->get(LoggerInterface::class)->error('Pesthub using cached data due to error: '. $ex->getMessage());
            } else {
                throw $ex;
            }
        }
        $result = $pestHub->getPestContent($url);
        if (is_array($pestHub->data)) { 
            $orgId = Config::inst()->get(PestHub::class, 'organisationid');
            Requirements::javascript('https://pw.gurudigital.nz/WebAPI/PanelScript?organisationId='. $orgId);
            Requirements::css('https://pw.gurudigital.nz/theme/styles/webapi.css');
            Requirements::javascript('gurudigital/pesthub: client/pwscript.js');
        }
        return $result;
    }
}

?>
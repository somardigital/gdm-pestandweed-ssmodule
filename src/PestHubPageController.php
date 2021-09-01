<?php 

namespace gurudigital\pesthub; 

use SilverStripe\View\Requirements;

class PestHubPageController extends \PageController 
{
    /*
    * Get current url without query string
    *
    * @return string Url 
    */
    public function getUrl() {
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
            $url = "https://";   
        else  
            $url = "http://";   
        // Append the host(domain name, ip) to the URL.   
        $url.= $_SERVER['HTTP_HOST'];   
        // Append the requested resource location to the URL   
        $url.= strtok($_SERVER["REQUEST_URI"], '?');   
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
        $pestHub = new PestHub();
        $data = $pestHub->getPestData($url);
        $result = $pestHub->getPestContent($url);
        if (!property_exists($data, "Error")) { 
            Requirements::javascript('https://pw.gurudigital.nz/WebAPI/PanelScript?organisationId=4');
            Requirements::css('https://pw.gurudigital.nz/theme/styles/webapi.css');
            Requirements::javascript('gurudigital/pesthub: client/pwscript.js');
        }
        return $result;
    }
}

?>
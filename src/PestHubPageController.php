<?php 

namespace gdmedia\pestsandweeds; 

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
    * depending on the query string
    */
    public function getPestContent() {
        Requirements::javascript('https://pw.gurudigital.nz/WebAPI/PanelScript?organisationId=4');
        Requirements::css('https://pw.gurudigital.nz/theme/styles/webapi.css');
        Requirements::javascript('gdmedia/pestsandweeds: client/pwscript.js');
        $url = $this->getUrl();
        $pestHub = new PestHub();
        return $pestHub->getPestContent($url);
    }
}

?>
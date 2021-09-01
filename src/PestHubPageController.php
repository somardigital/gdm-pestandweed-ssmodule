<?php 

namespace gdmedia\pestsandweeds; 

use SilverStripe\View\Requirements;

class PestHubPageController extends \PageController 
{
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

    public function getPestContent() {
        Requirements::javascript('https://pw.gurudigital.nz/WebAPI/PanelScript?organisationId=4');
        Requirements::css('https://pw.gurudigital.nz/theme/styles/webapi.css');
        Requirements::javascript('gdmedia/pestsandweeds: client/pwscript.js');
        $url = $this->getUrl();
        return PestHub::getPestContent($url);
    }
}

?>
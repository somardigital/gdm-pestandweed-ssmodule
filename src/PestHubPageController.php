<?php 

namespace gdmedia\pestsandweeds; 

use SilverStripe\View\Requirements;

class PestHubPageController extends \PageController 
{
    public function getPestContent() {
        Requirements::javascript('https://pw.gurudigital.nz/WebAPI/PanelScript?organisationId=4');
        Requirements::css('https://pw.gurudigital.nz/theme/styles/webapi.css');
        Requirements::javascript('gdmedia/pestsandweeds: client/pwscript.js');
        return PestHub::getPestContent();
    }
}

?>
<?php 

namespace gurudigital\pesthub; 

use SilverStripe\Core\Config\Config;
/*
* Access the pest & weeds API 
*/
class PestHub {
    /*
    * Get a pest when pwid is in query string of url
    *
    * @param string Request url
    *
    * @return object {
    *   'Id' => int,
    *   'CommonName' => string,
    *   'ScientificNames' => string,
    *   'Summary' => string,
    *   'Url' => string,
    *   'PrimaryImageThumbUrl' => string,
    *   'Content' => string
    * }
    */
    private function getRequestedPest($url) {
        $result = null;
        $data = PestHub::getPestData($url);
        if (isset($_GET["pwid"])) {
            foreach ($data as $item)
            {
                if ($item->Id . "" == $_GET["pwid"]) {
                    $result = $item;
                }
            }
        }
        return $result;
    }

    /* 
    * Get Pest and weed data from API
    * Cache in temp directory for 1 hour 
    *
    * @param string url The full url of the Pest Hub page
    *
    * @return array [
    *   'Id' => int,
    *   'CommonName' => string,
    *   'ScientificNames' => string,
    *   'Summary' => string,
    *   'Url' => string,
    *   'PrimaryImageThumbUrl' => string,
    *   'Content' => string
    * ]
    */
    public function getPestData($url) {
        $refresh = false;
        $data = json_decode("{\"Error\":\"Incorrect configuration\"}");
        $panelsFile = sys_get_temp_dir() . "/gdmediapestandweedsdata.json";
        $fileexists = file_exists($panelsFile);
        if ($fileexists) {
            if (time()-filemtime($panelsFile) > 3600) {
                $refresh = true;
            }
        } else {
            $refresh = true;
        }
        $output = "";
        if ($refresh) {
            $ch = curl_init();
            $orgId = Config::inst()->get(PestHub::class, 'organisationid');
            $apiKey = Config::inst()->get(PestHub::class, 'apikey');
            curl_setopt($ch, CURLOPT_URL, "https://pw.gurudigital.nz/webAPI/GetAllPestsAndWeeds?organisationId=" . $orgId . "&baseUrl=". $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey:" . $apiKey]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            $output = curl_exec($ch);
            $ok = true;
            if (curl_errno($ch)) {
                $ok = false;
                if ($fileexists) {
                    $output = file_get_contents($panelsFile);
                }                
            } else {
                $data = json_decode($output);
                if (property_exists($data, "Error")) {
                    $ok = false;
                }
            }
            curl_close($ch);
            if ($ok) {
                file_put_contents($panelsFile, $output, LOCK_EX);
                $data = json_decode($output);
            }
        } else {
            $output = file_get_contents($panelsFile);
            $data = json_decode($output);
        }
        return $data;
    }

    /* 
    * Get HTML of list of Pests & Weeds
    *
    * @param string url The full url of the Pest Hub page
    *
    * @return string HTML
    */
    public function getPestContent($url) {
        $content = "";
        $data = PestHub::getPestData($url);
        if (property_exists($data, "Error")) {
            $content = "<div style=\"color:red;\">" . $data->Error . "</div>";
        } else {
            $pest = PestHub::getRequestedPest($url);
            if ($pest != null) {
                $content = "<div id=\"pw-temp\">";
                $content .= "<h1>" . $pest->CommonName . "</h1>";
                $content .= "<h2>" . $pest->ScientificNames . "</h2>";
                $content .= "<div>" . $pest->Summary . "</div>";
                if ($pest->PrimaryImageThumbUrl) {
                    $content .= "<div><img src=\"" . $pest->PrimaryImageThumbUrl . "\" alt=\"" . $pest->CommonName . "\"/></div>";
                }
                $content .= "</div>";
            } else {
                $content = "<div id=\"pw-temp\">\n";
                $content .= "<div class=\"pw-container-fluid\">\n";
                $content .= " <div class=\"pw-row pw-no-gutters\">\n";
                foreach ($data as $item)
                {
                    $content .= "<div class=\"pw-col-auto pw-org-col\">\n";
                    $content .= " <div class=\"pw-organism\" data-id=\"380\">\n";
                    $content .= "  <a class=\"pw-link\" href=\"" . $item->Url . "\">\n";
                    $content .= "   <div class=\"pw-organism-inner\">\n";
                    if ($item->PrimaryImageThumbUrl) {
                        $content .= "     <div class=\"pw-image\">\n";
                        $content .= "       <img src=\"" . $item->PrimaryImageThumbUrl . "\" alt=\"" . htmlentities($item->CommonName) . "\"/>\n";
                        $content .= "     </div>\n";
                    }
                    $content .= "     <div class=\"pw-content\">\n";
                    $content .= "        <div class=\"pw-title\">" . htmlentities($item->CommonName) . "</div>\n";
                    $content .= "        <div class=\"pw-description\">\n";
                    $content .= htmlentities($item->Summary);
                    $content .= "        </div>\n";
                    $content .= "      </div>\n";
                    $content .= "     </div>\n";
                    $content .= "   </a>\n";
                    $content .= " </div>\n";
                    $content .= "</div>\n";
                }
                $content .= " </div>";
                $content .= "</div>";
                $content .= "</div>";
            }
        }
        return $content;
    }
}

?>
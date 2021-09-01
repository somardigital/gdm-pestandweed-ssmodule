<?php 
namespace gdmedia\pestsandweeds; 

use SilverStripe\Core\Config\Config;

class PestHub {

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

    public function getPestData($url) {
        $refresh = false;
        $panelsFile = sys_get_temp_dir() . "/panels_pw_22.txt";
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
            $output = curl_exec($ch);
            if (curl_errno($ch)) {
                if ($fileexists) {
                    $output = file_get_contents($panelsFile);
                }                
            }
            curl_close($ch);
            file_put_contents($panelsFile, $output, LOCK_EX);
        } else {
            $output = file_get_contents($panelsFile);
        }
        $data = json_decode($output);
        return $data;
    }

    public function getPestContent($url) {
        $content = "";
        $data = PestHub::getPestData($url);
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

        return $content;
    }
}

?>
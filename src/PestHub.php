<?php 

namespace gurudigital\pesthub; 

use SilverStripe\Core\Config\Config;
use SilverStripe\Control\Director;
use SilverStripe\Core\Flushable;
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

/*
* Access the pest & weeds API 
*/
class PestHub implements Flushable {
    const cacheFile = TEMP_PATH . "/" . "gdmediapestandweedsdata.json";

    public $data;
    private function __construct() {

    }

    public static function fromUrl($url) {
        $instance = new self();
        $instance->getPestData($url);
        return $instance;
    }


    public static function fromData($data) {
        $instance = new self();
        $instance->data = $data;
        return $instance;
    }


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
        if (isset($_GET["pwid"])) {
            $id = (int)$_GET["pwid"];
            if ($id > 0) {
                foreach ($this->data as $item)
                {
                    if ($item->Id == $id) {
                        $result = $item;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    public static function flush(){
        if (file_exists(PestHub::cacheFile)) {
            if (!unlink(PestHub::cacheFile)) {
                throw new Exception("Unable to delete PestHub cache file");
            }
        }
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
        $this->data = (object)["Error"=>"Incorrect configuration"];
        $fileexists = file_exists(PestHub::cacheFile);
        if ($fileexists) {
            if (time()-filemtime(PestHub::cacheFile) > 3600) {
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
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !Director::isDev());
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            $output = curl_exec($ch);
            $ok = true;
            if (curl_errno($ch)) {
                $ok = false;
                $ex = new PestHubException(curl_error($ch));
                if ($fileexists) {
                    $output = file_get_contents(PestHub::cacheFile);
                    $ex->data = json_decode($output);
                }            
                throw $ex;
            } else {
                $data = json_decode($output);
                if (is_object($data) && property_exists($data, "Error")) {
                    throw new PestHubException($data->Error);
                }
            }
            curl_close($ch);
            if ($ok) {
                file_put_contents(PestHub::cacheFile, $output, LOCK_EX);
                $this->data = json_decode($output);
            }
        } else {
            $output = file_get_contents(PestHub::cacheFile);
            $this->data = json_decode($output);
        }
        //return $data;
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
        if (is_object($this->data) && property_exists($this->data, "Error")) {
            $content = "<div style=\"color:red;\">" . $this->data->Error . "</div>";
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
                foreach ($this->data as $item)
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
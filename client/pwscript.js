window.pwurlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results == null){
        return null;
    }
    else {
        return decodeURI(results[1]) || 0;
    }
}
/*
The list of pests/weeds output by calling the API is displayed in pw-temp
This must remain displayed to be read by search engine bots.
When displaying an individual pest/weed or after "View Results" is clicked 
the list in pw-temp is hidden and the data supplied from the pest&weeds site displayed  
*/
document.addEventListener("DOMContentLoaded", function() {
    if (document.getElementById("pw-temp") && document.getElementById("pw-panel")) {
        if (window.pwurlParam('sort') !== null || window.pwurlParam('tags') !== null || window.pwurlParam('classification') !== null|| window.pwurlParam('pwid') !== null) {
            document.getElementById("pw-temp").style = "display:none;";
            document.getElementById("pw-panel").style = "";
        } else {
            document.addEventListener("click", function(e) {
                document.getElementById("pw-temp").style = "display:none;";
                document.getElementById("pw-panel").style = "";
                if (e.target.classList.contains("pw-results-link")) {
                    document.getElementById("pw-panel").scrollIntoView();
                }
            });
        }  
    }
});
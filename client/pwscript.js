window.pwurlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results == null){
       return null;
    }
    else {
       return decodeURI(results[1]) || 0;
    }
}
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
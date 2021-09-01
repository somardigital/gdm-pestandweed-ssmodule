$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results == null){
       return null;
    }
    else {
       return decodeURI(results[1]) || 0;
    }
}
if ($.urlParam('sort') !== null || $.urlParam('tags') !== null || $.urlParam('classification') !== null|| $.urlParam('pwid') !== null) {
    $("#pw-temp").css("display", "none");
    $("#pw-panel").show();
} else {
    $(document).on("click", function(e) {
        $("#pw-temp").css("display", "none");
        $("#pw-panel").show();
        if ($(e.target).hasClass("pw-results-link")) {
            document.getElementById("pw-panel").scrollIntoView();
        }
    });
}
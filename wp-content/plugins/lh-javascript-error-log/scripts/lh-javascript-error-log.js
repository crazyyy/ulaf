(function(){
    
    if (document.getElementById("lh_js_error_log-script").getAttribute("data-ajaxurl") && document.getElementById("lh_js_error_log-script").getAttribute("data-nonce")){
    var throttle = 0;
    
    console.log('logging allowed');

    window.onerror = function(msg, url, line){
        // Return if we've sent more than 10 errors.
        throttle++;
        if (throttle > 10) return;




let data = new FormData();
data.append('action', 'lh_js_error_log');
data.append('msg', msg);
data.append('url', url);
data.append('line', line);

if (document.getElementById("lh_js_error_log-script").getAttribute("data-current_url")){
    
data.append('current_url', document.getElementById("lh_js_error_log-script").getAttribute("data-current_url"));    
    
}


data.append('nonce', document.getElementById("lh_js_error_log-script").getAttribute("data-nonce"));



let result = navigator.sendBeacon(document.getElementById("lh_js_error_log-script").getAttribute("data-ajaxurl"), data);
        
        
    };
    
    }
})();
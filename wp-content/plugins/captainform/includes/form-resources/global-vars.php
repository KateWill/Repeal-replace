<script type="text/javascript">
    var captainformDomReady=function(e){var t=!1,n=function(){document.addEventListener?(document.removeEventListener("DOMContentLoaded",d),window.removeEventListener("load",d)):(document.detachEvent("onreadystatechange",d),window.detachEvent("onload",d))},d=function(){t||!document.addEventListener&&"load"!==event.type&&"complete"!==document.readyState||(t=!0,n(),e())};if("complete"===document.readyState)e();else if(document.addEventListener)document.addEventListener("DOMContentLoaded",d),window.addEventListener("load",d);else{document.attachEvent("onreadystatechange",d),window.attachEvent("onload",d);var o=!1;try{o=null==window.frameElement&&document.documentElement}catch(a){}o&&o.doScroll&&!function c(){if(!t){try{o.doScroll("left")}catch(d){return setTimeout(c,50)}t=!0,n(),e()}}()}};
    captainformDomReady(function() {
        if (document.getElementById('captainform_js_global_vars') == null) {
            append_element({
                elementType: "script",
                type: "text/javascript",
                id: "captainform_js_global_vars",
                textContent: 'var frmRef=""; try { frmRef=window.top.location.href; } catch(err) {}; var captainform_servicedomain="<?php echo $captainform_servicedomain;?>";var cfJsHost = "https://";',
            });
        }
    });
</script>
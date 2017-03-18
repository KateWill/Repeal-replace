<script id="{{ID}}" type="text/javascript">
    var customVarsMF = '{{CUSTOMVARS}}';
    var captainform_theme_style = '{{STYLE}}';
    captainformDomReady(function(){
        if(document.getElementById('captainform_easyxdmjs') == null)
        {
            append_element({
                elementType: "script",
                type: "text/javascript",
                id: "captainform_easyxdmjs",
                src: cfJsHost + captainform_servicedomain + "/includes/easyXDM.min.js",
            });
        }
        if(document.getElementById('iframeresizer_embedding_system') == null)
        {
            append_element({
                elementType: "script",
                type: "text/javascript",
                id: "iframeresizer_embedding_system",
                src: cfJsHost + captainform_servicedomain + "/modules/captainform/js/iframe_resizer/3.5/iframeResizer.min.js",
            });
        }
        append_element({
            elementType: "script",
            type: "text/javascript",
            id: "jsform-{{ID}}",
            src: cfJsHost + captainform_servicedomain + "/jsform-{{ID}}.js?" + customVarsMF + captainform_theme_style,
            replacing: document.getElementById("{{ID}}"),
        });
    });
</script>
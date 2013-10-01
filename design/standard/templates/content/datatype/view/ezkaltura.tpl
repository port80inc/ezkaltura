
<div id="kaltura_list"></div>
{def $server_data = get_env_server('HTTP_HOST')}
{literal}
<script type="text/javascript"> 
<!--
    $.ajax({
      type: "GET",
      url: "http://{/literal}{concat($server_data.env_data,'/',$server_data.current_site_access)}{literal}/kaltura/browse/{/literal}{concat($attribute.contentobject_id,'/',$attribute.id,'/',$attribute.version,'/',2)}{literal}",
      success: function(html){
        document.getElementById('kaltura_list').innerHTML = html;
      }
    });
    
-->
</script>

{/literal}

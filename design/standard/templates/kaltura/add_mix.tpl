<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>

<div class="box-header">
	<div class="box-tc">
		<div class="box-ml">
			<div class="box-mr">
				<div class="box-tl">
					<div class="box-tr">
                        <h1 class="context-title">{'Add Mix'|i18n( 'ezkaltura' )}</h1>
						<div class="header-mainline"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="kcwWrap" align="center" style="margin: 20px;">
    <div id="kcw"></div>
</div>
{include uri='design:kaltura/info.tpl' user=$user}
{literal}
<script type="text/javascript">
    var params = {
        allowScriptAccess: "always",
        allowNetworking: "all",
        wmode: "opaque"
    };

    var flash_vars = {/literal}{$flash_vars}{literal}
    swfobject.embedSWF("{/literal}{$server_url}{literal}/kcw/ui_conf_id/36200", "kcw", "680", "360", "9.0.0", false, flash_vars, params);
</script>
{/literal}

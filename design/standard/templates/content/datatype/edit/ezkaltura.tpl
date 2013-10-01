<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script type="text/javascript" src="/extension/ezkaltura/design/standard/javascript/nyroModal.js"></script>

<div id="modal_content">

{def $partner_id = $attribute.contentclass_attribute.data_int1}
{def $server_url = $attribute.contentclass_attribute.data_text1}
{def $admin_secret = $attribute.contentclass_attribute.data_text4}
    
{def $server_data = get_env_server('HTTP_HOST')}
{def $sess_id = get_kaltura_session_key($partner_id,$server_url,$admin_secret)}
{if $sess_id}
<div id="kaltura_list"></div>

<div align="center" style="display: none;" id="modal_kcw">

<div id="kcw"></div>

</div>
</div>
{literal}
<script type="text/javascript"> 
<!--
	var params = { 
		allowScriptAccess: "always",
		allowNetworking: "all",
		bgcolor: "#DBE3E9",
		quality: "high"
	};
	// 登録APIフラッシュの出力
	var flashVars = {"partnerId":{/literal}{$partner_id}{literal},"sessionId":"{/literal}{$sess_id}{literal}","afterAddEntry":"onContributionWizardAfterAddEntry","kshow_id":"-1","maxUploads":"1","close":"onContributionWizardClose"};
    swfobject.embedSWF("{/literal}{$server_url}{literal}kcw/ui_conf_id/1002225", "kcw", "680", "360", "9.0.0", false, flashVars, params);

    function onContributionWizardClose() {
        // モーダルを削除
        $.nyroModalRemove();
    }

	// 登録時のコールバック
	function onContributionWizardAfterAddEntry(entries) {
		var str = '?mode=insert&contentobject_id={/literal}{$attribute.contentobject_id}{literal}&contentobject_attribute_id={/literal}{$attribute.id}{literal}&version={/literal}{$attribute.version}{literal}';
		var ids = '';
		// 以下でクエリストリングを作りこむ
        for(var i = 0; i < entries.length; i++) {
        	str = str + '&entry_id[]=' + entries[i].entryId;
        }
		// データベース登録
		$.ajax({
		  type: "GET",
		  url: "http://{/literal}{concat($server_data.env_data,'/',$server_data.current_site_access)}{literal}/kaltura/db" + str,
		  success: function(html){
		    getKaltura_img_list();
		  }
		});

	}

	// 動画登録一覧
	function getKaltura_img_list() {
		$.ajax({
		  type: "GET",
		  url: "http://{/literal}{concat($server_data.env_data,'/',$server_data.current_site_access)}{literal}/kaltura/browse/{/literal}{concat($attribute.contentobject_id,'/',$attribute.id,'/',$attribute.version,'/',1)}{literal}",
		  success: function(html){
		    document.getElementById('kaltura_list').innerHTML = html;
            $('#modal_content a').nyroModal();
		  }
		});
	}
	// 削除処理
	function doDelete(){
		$.ajax({
		  type: "GET",
		  url: "http://{/literal}{concat($server_data.env_data,'/',$server_data.current_site_access)}{literal}/kaltura/db?mode=delete&contentobject_id={/literal}{$attribute.contentobject_id}{literal}&contentobject_attribute_id={/literal}{$attribute.id}{literal}&version={/literal}{$attribute.version}{literal}",
		  success: function(html){
		    getKaltura_img_list();
            $('#modal_content a').nyroModal();
		  }
		});
	}
	// 削除確認
	function isConfirm(id, mode, datas){
		if (window.confirm('{/literal}{'Removed from the draft.'|i18n( 'ezkaltura' )}{literal}\n{/literal}{'Are you sure?'|i18n( 'ezkaltura' )}{literal}')){
        	doDelete(id);
		}
	}
-->
</script>
<script type="text/javascript">
<!--
window.onload=function(){ 
	getKaltura_img_list();
}
-->
</script>

{/literal}

{else}
	<div align="center" style="padding:80px 0px 80px 0px;">{'Because they are not properly set the KALTURA, you can not view the video uploader.'|i18n( 'ezkaltura' )}<br />
	{'Please do re-set KALTURA class.'|i18n( 'ezkaltura' )}</div>
	
	
{/if}



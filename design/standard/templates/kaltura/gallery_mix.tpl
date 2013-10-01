<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
<script type="text/javascript" src="/extension/ezkaltura/design/standard/javascript/nyroModal.js"></script>

<div class="box-header">
	<div class="box-tc">
		<div class="box-ml">
			<div class="box-mr">
				<div class="box-tl">
					<div class="box-tr">
                        <h1 class="context-title">{'Gallery Media'|i18n( 'ezkaltura' )}</h1>
						<div class="header-mainline"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="box-content">

    <div class="context-toolbar">
        <div class="button-left">
            <p class="table-preferences">
                {if eq($page_size, 10)}
                    <span class="current">10</span>
                {else}
                    <a title="{'Show 10 items per page.'|i18n( 'design/admin/node/view/full' )}" href={concat('kaltura/gallery_mix/', $page, '/10/', $view_mode)|ezurl}>10</a>
                {/if}
                {if eq($page_size, 25)}
                    <span class="current">25</span>
                {else}
                    <a title="{'Show 25 items per page.'|i18n( 'design/admin/node/view/full' )}" href={concat('kaltura/gallery_mix/', $page, '/25/', $view_mode)|ezurl}>25</a>
                {/if}
                {if eq($page_size, 50)}
                    <span class="current">50</span>
                {else}
                    <a title="{'Show 50 items per page.'|i18n( 'design/admin/node/view/full' )}" href={concat('kaltura/gallery_mix/', $page, '/50/', $view_mode)|ezurl}>50</a>
                {/if}
            </p>
        </div>
        <div class="button-right">
            <p class="table-preferences">
                {if eq($view_mode, 1)}
                    <span class="current">{'Thumbnail'|i18n( 'design/admin/node/view/full' )}</span>
                {else}
                    <a title="{'Display sub items as thumbnails'|i18n('design/admin/content/browse')}" href={concat('kaltura/gallery_mix/', $page, '/',$page_size,'/1')|ezurl}>{'Thumbnail'|i18n( 'design/admin/node/view/full' )}</a>
                {/if}
                {if eq($view_mode, 2)}
                    <span class="current">{'Detailed'|i18n( 'design/admin/node/view/full' )}</span>
                {else}
                    <a title="{'Display sub items using a detailed list.'|i18n( 'design/admin/node/view/full' )}" href={concat('kaltura/gallery_mix/', $page, '/',$page_size,'/2')|ezurl}>{'Detailed'|i18n( 'design/admin/node/view/full' )}</a>
                {/if}
                
            </p>
        </div>
    </div>
    <div style="clear: both;"></div>

    <div style="margin: 20px 0px 30px 0px;">
        {def $num = 1}
        {if eq($view_mode, 1)}
            <div title="" class="list-thumbnails">
            {if gt($response|count,0)}
                {foreach $response as $kaltura}
            <div class="list-thumbnails-item">
                {def $is_contentobject = 0}
                {def $contentobject_attribute_id = 0}
                {def $version = 0}
                {if gt(count($kaltura.ezkaltura),0)}
                    {set $is_contentobject = 1}
                    {set $contentobject_attribute_id = $kaltura.ezkaltura.contentobject_attribute_id}
                    {set $version = $kaltura.ezkaltura.version}
                {/if}
                <div align="right"><a href="#" onclick="isConfirm({$contentobject_attribute_id}, '{$kaltura.id}', {$version}, {$is_contentobject})">×</a></div>

                <div class="content-view-thumbnail">
                    {if gt(count($kaltura.ezkaltura),0)}
                    <a href={$kaltura.ezkaltura.url_alias|ezurl}>
                    {/if}
                        <img width="151" height="85" title="{$kaltura.name}" alt="{$kaltura.name}" style="border: 0px  ;" src="{concat($kaltura.thumbnailUrl,'/width/320')}">
                    {if gt(count($kaltura.ezkaltura),0)}
                    </a>
                    {/if}
                </div>
                <div class="controls">
                    <p>
                    {$kaltura.name}
                    </p>
                    {if gt(count($kaltura.ezkaltura),0)}
                    <p>
                    <a href={$kaltura.ezkaltura.url_alias|ezurl}>{$kaltura.ezkaltura.object_name}</a>
                    </p>
                    {/if}
                    <div id="modal_content" style="padding-bottom: 5px;">
                        <a class="nyroModal" href="#movie_link{$kaltura.id}">
                            {'Preview'|i18n( 'ezkaltura' )}
                        </a>
                    </div>
                    <div style="display: none;" id="movie_link{$kaltura.id}"> 
                    {$kaltura.object_tag}
                    </div>
                 </div>
            </div>
                {/foreach}
            {/if}
            </div>
        {else}
        <table cellspacing="0" class="list">
            <tr>
                <th>{'Preview'|i18n( 'ezkaltura' )}</th>
                <th>{'Entry ID'|i18n( 'ezkaltura' )}</th>
                <th>{'Object Info'|i18n( 'ezkaltura' )}</th>
                <th>{'Media Name'|i18n( 'ezkaltura' )}</th>
                <th>{'Status'|i18n( 'ezkaltura' )}</th>
                <th>{'Media Type'|i18n( 'ezkaltura' )}</th>
                <th>{'Action'|i18n( 'ezkaltura' )}</th>
            </tr>
            {if gt($response|count,0)}
                {foreach $response as $kaltura}
            <tr {if eq($num,2)}style="background-color: #f5f5f5;"{/if}>
                <td style="width: 120px; text-align: center;">
                <img width="100" alt="Preview" src="{concat($kaltura.thumbnailUrl,'/width/300/')}" />
                </td>
                <td style="vertical-align: middle;">{$kaltura.id}</td>
                <td style="vertical-align: middle;">
                    {if gt(count($kaltura.ezkaltura),0)}
                        {'Class Identifier'|i18n( 'ezkaltura' )}：{$kaltura.ezkaltura.class_identifier}<br />
                        {'ContentObject Name'|i18n( 'ezkaltura' )}：<a href={$kaltura.ezkaltura.url_alias|ezurl}>{$kaltura.ezkaltura.object_name}</a><br />
                        {'Version'|i18n( 'ezkaltura' )}：{$kaltura.ezkaltura.version}<br />
                    {else}
                        {'No Data'|i18n( 'ezkaltura' )}
                    {/if}
                </td>
                <td style="vertical-align: middle;">{$kaltura.name}</td>
                <td colspan="" style="vertical-align: middle;">{$kaltura.status|i18n( 'ezkaltura/status' )}</td>
                <td style="vertical-align: middle;">MIX</td>
                <td style="vertical-align: middle;">
                    <div id="modal_content">
                        <a class="nyroModal" href="#movie_edit{$kaltura.id}">
                            {'Edit'|i18n( 'ezkaltura' )}
                        </a><br />
                        <a class="nyroModal" href="#movie_link{$kaltura.id}">
                            {'Preview'|i18n( 'ezkaltura' )}
                        </a>
                    </div>
                    {def $is_contentobject = 0}
                    {def $contentobject_attribute_id = 0}
                    {def $version = 0}
                    {if gt(count($kaltura.ezkaltura),0)}
                        {set $is_contentobject = 1}
                        {set $contentobject_attribute_id = $kaltura.ezkaltura.contentobject_attribute_id}
                        {set $version = $kaltura.ezkaltura.version}
                    {/if}
                    <a href="#" onclick="isConfirm({$contentobject_attribute_id}, '{$kaltura.id}', {$version}, {$is_contentobject})">
                        {'Delete'|i18n( 'ezkaltura' )}
                    </a>
                <div style="display: none;" id="movie_link{$kaltura.id}"> 
                    {$kaltura.object_tag}
                </div>
                <div style="display: none;" id="movie_edit{$kaltura.id}">
                    <div id="kcw{$kaltura.id}"></div>
                </div>
                {literal}
				<script type="text/javascript">
					var params = {
						allowscriptaccess: "always",
						allownetworking: "all",
						wmode: "opaque"
					};
					
					var flashVars = {/literal}{$kaltura.flash_vars}{literal};
					swfobject.embedSWF("{/literal}{$server_url}{literal}/kse/ui_conf_id/36300", "kcw{/literal}{$kaltura.id}{literal}", "890", "546", "9.0.0", false, flashVars, params);
				</script>
                {/literal}
                </td>
            </tr>
                {if eq($num, 2)}
                    {set $num = 1}
                {else}
                    {set $num = inc($num)}
                {/if}
                {/foreach}
            {else}
            <tr>
                <td colspan="8" style="vertical-align: middle;text-align: center;">
                    {'No Data Was Uploaded.'|i18n( 'ezkaltura' )}
                </td>
            </tr>
            {/if}
        </table>
        {/if}
        <div style="clear: both;"></div>
        <div class="pagenavigator" style="margin-top: 20px;">
            {def $total   = ceil(div($response_count, $page_size))}
            {def $endSize = 1}
            {def $midSize = 2}
            {def $dots    = false()}
            <p>
                <span class="previous">{if gt($page, 1)}<a href={concat('kaltura/gallery_mix/',sub($page, 1), '/', $page_size,'/',$view_mode)|ezurl}">{/if}<span {if eq($page, 1)}class="text disabled"{/if}>≪&nbsp;前へ</span>{if gt($page, 1)}</a>{/if}</span>
                <span class="next">{if and(eq($total, 0)|not, eq($total, $page)|not)}<a href={concat('kaltura/gallery_media/',sum($page, 1), '/', $page_size,'/',$view_mode)|ezurl}>{/if}<span {if eq($total, $page)}class="text disabled"{/if}>次へ&nbsp;≫</span>{if and(eq($total, 0)|not, eq($total, $page)|not)}</a>{/if}</span>
                <span class="pages">
                {if gt($total, 1)}
                    {for 1 to $total as $i}
                        {if eq($i, $page)}
                            <span class="current">{$i}</span>
                            {set $dots = true()}
                        {else}
                            {if or( le($i, $endSize), and($page, ge($i, sub($page, $midSize)), le($i, sum($page, $midSize))), gt($i, sub($total, $endSize)))}
                                <span class="other"><a href={concat('kaltura/gallery_mix/', $i, '/', $page_size,'/',$view_mode)|ezurl}>{$i}</a></span>
                                {set $dots = true()}
                            {elseif $dots}
                                <span class="other">...</span>
                                {set $dots = false()}
                            {/if}
                        {/if}
                    {/for}
                {/if}
                </span>
            </p>
        </div>
    </div>
</div>
{* <!-- ユーザー情報 --> *}
{include uri='design:kaltura/info.tpl' user=$user}

{* <!-- サーバー情報を取得する --> *}
{def $server_data = get_env_server('HTTP_HOST')}

{literal}
<script type="text/javascript"> 
<!--
	// 削除処理
	function doDelete(contentobject_attribute_id, entry_id, version){
        location.href = 'http://{/literal}{concat($server_data.env_data,'/',$server_data.current_site_access)}{literal}/kaltura/delete?entry_id=' + entry_id + '&contentobject_attribute_id=' + contentobject_attribute_id + '&version=' + version + '&is_delete_other_version=1&media_type=mixing';
	}
	// 削除確認する
	function isConfirm(contentobject_attribute_id, entry_id, version, is_contentobject){
		if (window.confirm('{/literal}{'Delete the selected data.'|i18n( 'ezkaltura' )}{literal}\n{/literal}{'Are you sure?'|i18n( 'ezkaltura' )}{literal}')){
            // オブジェクトと紐づきのある動画は、再度確認メッセージを出力
            if(is_contentobject) {
        		if (window.confirm('{/literal}{'Remove from the object.'|i18n( 'ezkaltura' )}{literal}\n{/literal}{'Are you sure?'|i18n( 'ezkaltura' )}{literal}')){
                    // 削除実行
                	doDelete(contentobject_attribute_id, entry_id, version);
                }
            } else {
                // 削除実行
            	doDelete(contentobject_attribute_id, entry_id, version);
            }
		}
	}
-->
</script>

<script type="text/javascript">
<!--
window.onload=function(){ 
    $('#modal_content a').nyroModal();
}
</script>
{/literal}

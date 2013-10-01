<fieldset style="margin: 10px 0px 0px 0px;">
    <legend>{'Kaltura Files'|i18n( 'ezkaltura' )}</legend>

{def $num = 1}
{if eq($mode ,1)}
<div style="margin: 0px 0px 15px 0px;">
<a name="kaltura_regist" class="nyroModal button" href="#modal_kcw">{'Upload'|i18n( 'ezkaltura' )}</a>
</div>
{/if}

{if eq($mode ,2)}
    {if gt($kaltura_data|count,0)}
        {foreach $kaltura_data as $kaltura}
        <div style="margin: 10px 0px 20px 0px;" align="center">
            {$kaltura.serialized_metadata.object_tag}
        </div>
        {/foreach}
    {/if}
{/if}

<table cellspacing="0" class="list">
    <tr>
        <th>{'Preview'|i18n( 'ezkaltura' )}</th>
        <th>{'Entry ID'|i18n( 'ezkaltura' )}</th>
        <th>{'Media Name'|i18n( 'ezkaltura' )}</th>
        <th>{'Status'|i18n( 'ezkaltura' )}</th>
        <th>{'Media Type'|i18n( 'ezkaltura' )}</th>
        <th>{'Size'|i18n( 'ezkaltura' )}</th>
        <th>{'Duration'|i18n( 'ezkaltura' )}</th>
        {if eq($mode ,1)}
        <th>{'Action'|i18n( 'ezkaltura' )}</th>
        {/if}
    </tr>
    <tr>
    {if gt($kaltura_data|count,0)}
        {foreach $kaltura_data as $kaltura}
        <td style="width: 120px; text-align: center;">
        <img width="100" alt="Preview" src="{$kaltura.thumbnail_path}" />
        </td>
        <td style="vertical-align: middle;">{$kaltura.entry_id}</td>
        <td style="vertical-align: middle;">{$kaltura.movie_filename}</td>
        <td colspan="" style="vertical-align: middle;">{$kaltura.serialized_metadata.status|i18n( 'ezkaltura/status' )}</td>
        <td style="vertical-align: middle;">{$kaltura.serialized_metadata.mediaType|i18n( 'ezkaltura/mediatype' )}</td>
        <td style="vertical-align: middle;">
            {if ne($kaltura.serialized_metadata.mediaType,'AUDIO')}
                {if and($kaltura.serialized_metadata.width, $kaltura.serialized_metadata.height)}
                    {$kaltura.serialized_metadata.width} x {$kaltura.serialized_metadata.height}
                {/if}
            {else}
                {'No Data'|i18n( 'ezkaltura' )}
            {/if}
        </td>
        
        <td style="vertical-align: middle;">
            {if ne($kaltura.serialized_metadata.mediaType,'IMAGE')}
                {$kaltura.serialized_metadata.duration}
            {else}
                {'No Data'|i18n( 'ezkaltura' )}
            {/if}
        </td>
        {if eq($mode ,1)}
        <td style="vertical-align: middle;">
        <a class="nyroModal" href="#movie_link{$kaltura.movie_id}">
            {'Preview'|i18n( 'ezkaltura' )}
        </a>
        <div style="display: none;" id="movie_link{$kaltura.movie_id}"> 
            {$kaltura.serialized_metadata.object_tag}
        </div>
        </td>
        {else}
            {* <!--<a href="#" onclick="doDownload('{$kaltura.download_path}')">Download</a>--> *}
        {/if}

        {/foreach}
    {else}
        <td colspan="8" style="vertical-align: middle;text-align: center;">
            {'No Data Was Uploaded.'|i18n( 'ezkaltura' )}
        </td>
    {/if}
    </tr>
</table>
{if gt($kaltura_data|count,0)}
    {* <!-- 編集画面でのみ削除ボタンを有効 --> *}
    {if eq($mode ,1)}
    <div align="left" style="margin: 15px 0px 0px 0px;">
    <input type="button" onclick="isConfirm()" value="{'Delete'|i18n( 'ezkaltura' )}" class="button">
    </div>
    {/if}
{/if}

<br />

{if $error}
error:{$error|i18n( 'ezkaltura' )}<br />
{/if}
</fieldset>

{literal}
<style type="text/css">
td, th {
padding: 5px 5px 5px 5px;
}
</style>
{/literal}
<div class="box-header">
	<div class="box-tc">
		<div class="box-ml">
			<div class="box-mr">
				<div class="box-tl">
					<div class="box-tr">
                        <h1 class="context-title">{'Kaltura'|i18n( 'ezkaltura' )} {'Account'|i18n( 'ezkaltura' )} {'Config'|i18n( 'ezkaltura' )}</h1>
						<div class="header-mainline"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div align="center" style="margin: 15px 0px 10px 0px;">
    <div align="center" style="font-size: 12px; margin-bottom: 15px;">{'Please enter your account information.'|i18n( 'ezkaltura' )}</div>
    <form action="config" method="post">
        <table style="border: #efefef; border-style: solid;">
            <tr style="padding:100px;">
                <th>{'Kaltura Server URL'|i18n( 'ezkaltura/class' )}</th>
                <td><input type="text" name="server_url" value="{$params.server_url}" size="50"></td>
                <td>{'Examples'|i18n( 'ezkaltura' )}：http://www.kaltura.com/</td>
            </tr>
            <tr style="background-color: #f5f5f5;">
                <th>{'Partner ID'|i18n( 'ezkaltura/class' )}</th>
                <td><input type="text" name="partner_id" size="15" value="{$params.partner_id}"></td>
                <td>{'Examples'|i18n( 'ezkaltura' )}：100</td>
            </tr>
            <tr>
                <th>{'Email'|i18n( 'ezkaltura/class' )}</th>
                <td><input type="text" name="email"  size="40" value="{$params.email}"></td>
                <td>{'Examples'|i18n( 'ezkaltura' )}：my@example.com</td>
            </tr>
            <tr style="background-color: #f5f5f5;">
                <th>{'Password'|i18n( 'ezkaltura/class' )}</th>
                <td><input type="password" name="password"  size="20" value=""></td>
                <td></td>
            </tr>
            {* <!-- エラーの有無を確認し、あれば出力する--> *}
            {if $error}
            <tr>
                <td colspan="3">
                    {foreach $error as $key => $value}
                        <span style="color: #ff0000;">{$value|i18n( 'ezkaltura/error' )}</span><br />
                    {/foreach}
                </td>
            </tr>
            {elseif $is_success}
            <tr>
                <td colspan="3" style="background-color: #ff0000">
                    <span style="color: #ffffff;font-weight: bold;">{'Saved.'|i18n( 'ezkaltura/error' )}</span><br />
                </td>
            </tr>
            {/if}
        </table>
        <div style="margin-top: 10px;">
            <input type="submit" value="{'Config'|i18n( 'ezkaltura' )}">
        </div>
    </form>
</div>
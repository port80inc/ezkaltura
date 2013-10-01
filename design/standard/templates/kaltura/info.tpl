<fieldset style="margin: 50px 0px 50px 0px; text-align: left;">
    <legend>{'Kaltura Info'|i18n( 'ezkaltura' )}</legend>
    <table>
        <tr>
            <td>{'Kaltura Server URL'|i18n( 'ezkaltura/class' )}：</td>
            <td><a href="{$user.server_url}">{$user.server_url}</a></td>
        </tr>
        <tr>
            <td>{'Partner ID'|i18n( 'ezkaltura/class' )}：</td>
            <td>{$user.partner_id}</td>
        </tr>
        <tr>
            <td>{'Email'|i18n( 'ezkaltura/class' )}：</td>
            <td>{$user.email}</td>
        </tr>
    </table>
</fieldset>

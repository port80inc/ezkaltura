
<div class="block">
    <label>{'Kaltura Server URL'|i18n( 'ezkaltura/class' )}:</label>
    <input type="text" name="ContentClass_ezkaltura_server_url_{$class_attribute.id}" value="{$class_attribute.data_text1}" />&nbsp;&nbsp;{'Examples'|i18n( 'ezkaltura' )}) http://www.kaltura.com/
</div>
<div class="block">
    <label>{'Partner Id'|i18n( 'ezkaltura/class' )}:</label>
    <input type="text" name="ContentClass_ezkaltura_partner_id_{$class_attribute.id}" value="{$class_attribute.data_int1}" />&nbsp;&nbsp;{'Examples'|i18n( 'ezkaltura' )}) 101
</div>
<div class="block">
    <label>{'Email'|i18n( 'ezkaltura/class' )}:</label>
    <input type="text" name="ContentClass_ezkaltura_email_{$class_attribute.id}" value="{$class_attribute.data_text2}" />&nbsp;&nbsp;{'Examples'|i18n( 'ezkaltura' )}) my@example.com
</div>
<div class="block">
    <label>{'Password'|i18n( 'ezkaltura/class' )} ( {'Please enter only if you change the server information.'|i18n( 'ezkaltura/class' )} ) :</label>
    <input type="text" name="ContentClass_ezkaltura_password_{$class_attribute.id}" value="" />
    <input type="hidden" name="ContentClass_ezkaltura_secret_{$class_attribute.id}" value="{$class_attribute.data_text3}" />
    <input type="hidden" name="ContentClass_ezkaltura_admin_secret_{$class_attribute.id}" value="{$class_attribute.data_text4}" />
</div>
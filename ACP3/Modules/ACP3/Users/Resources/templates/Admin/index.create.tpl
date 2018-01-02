{extends file="asset:System/layout.ajax-form.tpl"}

{block CONTENT_AJAX_FORM}
    <div class="tabbable">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-1" data-toggle="tab">{lang t="system|general"}</a></li>
            <li><a href="#tab-2" data-toggle="tab">{lang t="users|contact"}</a></li>
            <li><a href="#tab-3" data-toggle="tab">{lang t="users|address"}</a></li>
            <li><a href="#tab-5" data-toggle="tab">{lang t="users|privacy"}</a></li>
            <li><a href="#tab-6" data-toggle="tab">{lang t="users|pwd"}</a></li>
        </ul>
        <div class="tab-content">
            <div id="tab-1" class="tab-pane fade in active">
                {include file="asset:System/Partials/form_group.input_text.tpl" name="nickname" value=$form.nickname required=true maxlength=30 label={lang t="users|nickname"}}
                {include file="asset:System/Partials/form_group.input_text.tpl" name="realname" value=$form.realname maxlength=80 label={lang t="users|realname"}}
                {include file="asset:System/Partials/form_group.select.tpl" options=$gender required=true label={lang t="users|gender"}}
                {datepicker name="birthday" value=$birthday inputFieldOnly=true withTime=false label={lang t="users|birthday"}}
                {include file="asset:System/Partials/form_group.select.tpl" options=$roles required=true multiple=true label={lang t="permissions|roles"}}
                {include file="asset:System/Partials/form_group.button_group.tpl" options=$super_user required=true label={lang t="users|super_user"}}
            </div>
            <div id="tab-2" class="tab-pane fade">
                {foreach $contact as $row}
                    {include file="asset:System/Partials/form_group.input_text.tpl" name=$row.name value=$row.value maxlength=$row.maxlength label=$row.lang}
                {/foreach}
            </div>
            <div id="tab-3" class="tab-pane fade">
                {include file="asset:System/Partials/form_group.input_text.tpl" name="street" value=$form.street maxlength=80 label={lang t="users|address_street"}}
                {include file="asset:System/Partials/form_group.input_text.tpl" name="house_number" value=$form.house_number maxlength=5 label={lang t="users|address_house_number"}}
                {include file="asset:System/Partials/form_group.input_text.tpl" name="zip" value=$form.zip maxlength=5 label={lang t="users|address_zip"}}
                {include file="asset:System/Partials/form_group.input_text.tpl" name="city" value=$form.city maxlength=80 label={lang t="users|address_city"}}
                {include file="asset:System/Partials/form_group.select.tpl" options=$countries label={lang t="users|country"}}
            </div>
            <div id="tab-5" class="tab-pane fade">
                {include file="asset:System/Partials/form_group.button_group.tpl" options=$mail_display required=true label={lang t="users|display_mail"}}
                {include file="asset:System/Partials/form_group.button_group.tpl" options=$address_display required=true label={lang t="users|display_address"}}
                {include file="asset:System/Partials/form_group.button_group.tpl" options=$country_display required=true label={lang t="users|display_country"}}
                {include file="asset:System/Partials/form_group.radio.tpl" options=$birthday_display required=true label={lang t="users|birthday"}}
            </div>
            <div id="tab-6" class="tab-pane fade">
                {block PASSWORD_FIELDS}
                    {include file="asset:Users/Partials/password_fields.tpl" required=true}
                {/block}
            </div>
        </div>
    </div>
    {include file="asset:System/Partials/form_group.submit_split.tpl" form_token=$form_token back_url={uri args="acp/users"}}
{/block}
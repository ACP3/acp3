{extends file="asset:`$LAYOUT`"}

{block CONTENT}
    {if isset($error_msg)}
        {$error_msg}
    {/if}
    <form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8" class="form-horizontal" data-ajax-form="true" data-ajax-form-loading-text="{lang t="system|loading_please_wait"}">
        {include file="asset:System/Partials/form_group.select.tpl" options=$salutation emptyOptionLabel={lang t="newsletter|salutation_unspecified"} label={lang t="newsletter|salutation"}}
        <div class="form-group">
            <label for="first_name" class="col-sm-2 control-label">{lang t="newsletter|first_name"}</label>
            <div class="col-sm-10">
                <input class="form-control" type="text" name="first_name" id="first_name" maxlength="120" value="{$form.first_name}">
            </div>
        </div>
        <div class="form-group">
            <label for="last_name" class="col-sm-2 control-label">{lang t="newsletter|last_name"}</label>
            <div class="col-sm-10">
                <input class="form-control" type="text" name="last_name" id="last_name" maxlength="120" value="{$form.last_name}">
            </div>
        </div>
        {include file="asset:System/Partials/form_group.input_email.tpl" name="mail" value=$form.mail required=true maxlength=120 label={lang t="system|email_address"}}
        {event name="captcha.event.display_captcha"}
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" name="submit" class="btn btn-primary">{lang t="system|submit"}</button>
                <a href="{uri args="newsletter/archive/index"}" class="btn btn-link">{lang t="newsletter|missed_out_newsletter"}</a>
                {$form_token}
            </div>
        </div>
    </form>
    {javascripts}
        {include_js module="system" file="ajax-form"}
    {/javascripts}
{/block}

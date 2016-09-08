{extends file="asset:`$LAYOUT`"}

{block CONTENT}
    {if isset($error_msg)}
        {$error_msg}
    {/if}
    {redirect_message}
    <form action="{uri args="acp/feeds"}" method="post" accept-charset="UTF-8" class="form-horizontal" data-ajax-form="true" data-ajax-form-loading-text="{lang t="system|loading_please_wait"}">
        <div class="form-group">
            <label for="feed-image" class="col-sm-2 control-label">{lang t="feeds|feed_image"}</label>

            <div class="col-sm-10">
                <input class="form-control" type="text" name="feed_image" id="feed-image" value="{$form.feed_image}" maxlength="120">
            </div>
        </div>
        <div class="form-group">
            <label for="feed-type" class="col-sm-2 control-label required">{lang t="feeds|feed_type"}</label>

            <div class="col-sm-10">
                <select class="form-control" name="feed_type" id="feed-type" required>
                    {foreach $feed_types as $row}
                        <option value="{$row.value}"{$row.selected}>{$row.lang}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {include file="asset:System/Partials/form_group.submit.tpl" form_token=$form_token back_url={uri args="acp/feeds"}}
    </form>
    {javascripts}
        {include_js module="system" file="ajax-form"}
    {/javascripts}
{/block}

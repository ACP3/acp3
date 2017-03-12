{extends file="asset:System/Partials/form_group.base.tpl"}

{block FORM_GROUP_FORM_FIELD}
    <button type="submit" name="submit" class="btn btn-primary{if !empty($btn_class_submit)} {$btn_class_submit}{/if}">
        {if !empty($submitLabel)}
            {$submitLabel}
        {else}
            {lang t="system|submit"}
        {/if}
    </button>
    {if !empty($back_url)}
        <a href="{$back_url}" class="btn btn-default{if !empty($btn_class_back)} {$btn_class_back}{/if}">
            {lang t="system|cancel"}
        </a>
    {/if}
    {if !empty($form_token)}
        {$form_token}
    {/if}
{/block}

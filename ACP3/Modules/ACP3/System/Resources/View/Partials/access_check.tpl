{if $access_check.mode == 'link'}
    <a href="{$access_check.uri}"
       title="{$access_check.lang}">
        {if isset($access_check.class)}
            <i class="{$access_check.class}" aria-hidden="true"></i>
        {/if}
        {if isset($access_check.title)}
            {$access_check.title}
        {/if}
    </a>
{elseif $access_check.mode == 'button'}
    <button type="submit"
            class="{if isset($access_check.btn_class)}{$access_check.btn_class}{else}btn btn-link{/if}"
            title="{$access_check.lang}">
        {if isset($access_check.class)}
            <i class="{$access_check.class}" aria-hidden="true"></i>
        {/if}
        {if isset($access_check.title)}
            {$access_check.title}
        {/if}
    </button>
{/if}

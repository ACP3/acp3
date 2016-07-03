{extends file="asset:`$LAYOUT`"}

{block CONTENT}
    {if isset($pictures)}
        {if $overlay == 1}
            {foreach $pictures as $row}
                {if $row@index % 4 === 0}
                    <div class="row">
                {/if}
                <div class="col-sm-3">
                    <a href="{uri args="gallery/index/image/id_`$row.id`/action_normal"}" class="thumbnail" data-fancybox-group="gallery"{if !empty($row.description)} title="{$row.description|strip_tags}"{/if}>
                        <img src="{uri args="gallery/index/image/id_`$row.id`/action_thumb"}" alt="" width="{$row.width}" height="{$row.height}">
                    </a>
                </div>
                {if $row@last || $row@iteration is div by 4}
                    </div>
                {/if}
            {/foreach}
            {javascripts}
            {include_js module="gallery" file="frontend/index.pics" depends="fancybox"}
            {/javascripts}
        {else}
            {foreach $pictures as $row}
                {if $row@index % 4 === 0}
                    <div class="row">
                {/if}
                <div class="col-sm-3">
                    <a href="{uri args="gallery/index/details/id_`$row.id`"}" class="thumbnail">
                        <img src="{uri args="gallery/index/image/id_`$row.id`/action_thumb"}" alt="" width="{$row.width}" height="{$row.height}">
                    </a>
                </div>
                {if $row@last || $row@iteration is div by 4}
                    </div>
                {/if}
            {/foreach}
        {/if}
    {else}
        <div class="alert alert-warning text-center">
            <strong>{lang t="gallery|no_pictures"}</strong>
        </div>
    {/if}
{/block}

{extends file="asset:`$LAYOUT`"}

{block CONTENT}
    {if $overlay == 1}
        <div class="modal fade" id="js-modal-create" tabindex="-1" role="dialog" aria-labelledby="js-modal-create" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="p-3 text-center">
                        {icon iconSet="solid" icon="spinner" cssSelectors="svg-icon--spin"}
                    </div>
                </div>
            </div>
        </div>
        <p class="text-center">
            <a href="{uri args="guestbook/index/create"}" id="js-create-link" title="{lang t="guestbook|create"}">{lang t="guestbook|create"}</a>
        </p>
        {javascripts}
            {include_js module="guestbook" file="frontend/index.index"}
        {/javascripts}
    {else}
        <p class="text-center">
            <a href="{uri args="guestbook/index/create"}" title="{lang t="guestbook|create"}">{lang t="guestbook|create"}</a>
        </p>
    {/if}
    {redirect_message}
    {if !empty($guestbook)}
        {include file="asset:System/Partials/pagination.tpl" pagination=$pagination}
        {foreach $guestbook as $row}
            <article id="gb-entry-{$row.id}" class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>
                        {if $row.user_id !== null}
                            <a href="{uri args="users/index/view_profile/id_`$row.user_id`"}" title="{lang t="users|view_profile"}">{$row.name}</a>
                        {else}
                            {$row.name}
                        {/if}
                    </strong>
                    <div>
                        <time class="badge bg-primary rounded-pill" datetime="{date_format date=$row.date format="c"}">{date_format date=$row.date format=$dateformat}</time>
                        {if $row.website != ''}
                            <a href="{$row.website|prefix_uri}"
                               class="ms-2"
                               target="_blank"
                               rel="noopener nofollow"
                               title="{lang t="guestbook|visit_website"}">{icon iconSet="solid" icon="link"}</a>
                        {/if}
                        {if $row.mail != ''}
                            {mailto address=$row.mail encode="javascript" text={icon iconSet="solid" icon="envelope" cssSelectors="ms-2"}}
                        {/if}
                    </div>
                </div>
                <div class="card-body">
                    {$row.message|decorate}
                </div>
            </article>
        {/foreach}
        {include file="asset:System/Partials/pagination.tpl" pagination=$pagination}
    {else}
        {include file="asset:System/Partials/no_results.tpl"}
    {/if}
{/block}

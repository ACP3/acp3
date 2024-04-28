<section id="comments">
    <h3 class="text-center">{lang t="comments|comments"}</h3>
    {redirect_message}
    {if !empty($comments)}
        {include file="asset:System/Partials/pagination.tpl" pagination=$pagination}
        {foreach $comments as $row}
            <article class="card mb-3">
                <header class="card-header d-flex justify-content-between align-items-center">
                    <strong>
                        {if $row.user_id !== null}
                            <a href="{uri args="users/index/view_profile/id_`$row.user_id`"}"
                               title="{lang t="users|view_profile"}">
                                {$row.name}
                            </a>
                        {else}
                            {$row.name}
                        {/if}
                    </strong>
                    <time class="badge bg-primary rounded-pill" datetime="{date_format date=$row.date format="c"}">
                        {date_format date=$row.date format=$dateformat}
                    </time>
                </header>
                <div class="card-body">
                    {$row.message|decorate}
                </div>
            </article>
        {/foreach}
    {else}
        {include file="asset:System/Partials/no_results.tpl"}
    {/if}
</section>

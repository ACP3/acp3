<ul class="nav nav-list">
	<li class="nav-header">{lang t="news|latest_news"}</li>
{if isset($sidebar_news)}
{foreach $sidebar_news as $row}
	<li><a href="{uri args="news/details/id_`$row.id`"}" title="{$row.start} - {$row.headline}">{$row.headline_short}</a></li>
{/foreach}
{else}
	<li>{lang t="common|no_entries_short"}</li>
{/if}
</ul>
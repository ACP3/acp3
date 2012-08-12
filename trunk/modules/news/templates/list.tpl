<div class="container-fluid">
	<div class="row-fluid">
		<div class="span4">{if !empty($pagination)}{$pagination}{/if}</div>
{if isset($categories)}
		<div class="span4" style="text-align: center">
{if ACP3_Modules::check('newsletter', 'create')}
			<a href="{uri args="newsletter/create"}">{lang t="newsletter|create"}</a>
{/if}
		</div>
		<div class="span4" style="text-align: right">
			<form action="{uri args="news/list"}" method="post" class="form-inline">
				{$categories}
				<input type="submit" name="submit" value="{lang t="common|submit"}" class="btn">
			</form>
		</div>
{/if}
	</div>
</div>
{if isset($news)}
{foreach $news as $row}
<div class="news">
	<h3 class="header">
		{$row.headline}
	</h3>
	<div class="date">
{if $row.allow_comments}
		<div class="comments">
			<a href="{uri args="news/details/id_`$row.id`"}#comments">{lang t="comments|comments"}</a>
			<span>({$row.comments})</span>
		</div>
{/if}
		{$row.date}
	</div>
	<div class="content">
		{$row.text}
	</div>
</div>
{/foreach}
{else}
<div class="alert alert-block">
	<h5>{lang t="common|no_entries"}</h5>
</div>
{/if}
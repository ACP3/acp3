<form action="{uri args="acp/gallery/delete_gallery"}" method="post">
	<div id="adm-list">
		{check_access mode="link" action="gallery|create" uri="acp/gallery/create" icon="32/folder_image" width="32" height="32"}
		{check_access mode="link" action="gallery|settings" uri="acp/gallery/settings" icon="32/advancedsettings" width="32" height="32"}
		{check_access mode="input" action="gallery|delete_gallery" icon="32/cancel" lang="common|delete_marked"}
		<h2>{lang t="common|overview"}</h2>
	</div>
{if isset($redirect_message)}
{$redirect_message}
{/if}
{if isset($galleries)}
{$pagination}
{assign var="can_delete" value=modules::check("gallery", "delete_gallery")}
	<table class="acp-table">
		<thead>
			<tr>
{if $can_delete === true}
				<th><input type="checkbox" id="mark-all" value="1" class="checkbox"></th>
{/if}
				<th>{lang t="common|publication_period"}</th>
				<th>{lang t="gallery|title"}</th>
				<th>{lang t="gallery|pictures"}</th>
				<th style="width:3%">{lang t="common|id"}</th>
			</tr>
		</thead>
		<tbody>
{foreach $galleries as $row}
			<tr>
{if $can_delete === true}
				<td><input type="checkbox" name="entries[]" value="{$row.id}" class="checkbox"></td>
{/if}
				<td>{$row.period}</td>
				<td>{check_access mode="link" action="gallery|edit_gallery" uri="acp/gallery/edit_gallery/id_`$row.id`" title=$row.name}</td>
				<td>{$row.pictures}</td>
				<td>{$row.id}</td>
			</tr>
{/foreach}
		</tbody>
	</table>
{if $can_delete === true}
{mark name="entries"}
{/if}
{else}
	<div class="error">
		<h5>{lang t="common|no_entries"}</h5>
	</div>
{/if}
</form>
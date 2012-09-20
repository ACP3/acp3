{if isset($export)}
<pre>
{$export}
</pre>
{else}
{if isset($error_msg)}
{$error_msg}
{/if}
<script type="text/javascript">
function mark_options(action)
{
	var $elem = $('form #tables option');
	if (action == 'add') {
		$elem.attr('selected', true);
	} else {
		$elem.removeAttr('selected');
	}
}

$(function() {
	$('input[name="export_type"]').bind('click', function() {
		var $elem = $('#options-container');
		if ($(this).attr('id') == 'complete' || $(this).attr('id') == 'structure') {
			$elem.show();
		} else {
			$elem.hide();
		}
	}).filter(':checked').trigger('click');
});
</script>
<form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8" class="form-horizontal">
	<div class="tabbable">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#tab-1" data-toggle="tab">{lang t="system|sql_tables"}</a></li>
			<li><a href="#tab-2" data-toggle="tab">{lang t="system|export_options"}</a></li>
		</ul>
		<div class="tab-content">
			<div id="tab-1" class="tab-pane active">
				<div class="control-group">
					<label for="tables" class="control-label">{lang t="system|sql_tables"}</label>
					<div class="controls">
						<select name="tables[]" id="tables" multiple="multiple" class="span6" style="height:200px">
{foreach $tables as $row}
							<option value="{$row.name}"{$row.selected}>{$row.name}</option>
{/foreach}
						</select>
						<p class="help-block">
							<a href="javascript:mark_options('add')">{lang t="system|mark_all"}</a> <span>::</span> <a href="javascript:mark_options('remove')">{lang t="system|unmark_all"}</a>
						</p>
					</div>
				</div>
			</div>
			<div id="tab-2" class="tab-pane">
				<div class="control-group">
					<label for="file" class="control-label">{lang t="system|output"}</label>
					<div class="controls">
						<div class="btn-group" data-toggle="radio">
{foreach $output as $row}
							<input type="radio" name="output" id="{$row.value}" value="{$row.value}"{$row.checked}>
							<label for="{$row.value}" class="btn">{$row.lang}</label>
{/foreach}
						</div>
					</div>
				</div>
				<div class="control-group">
					<label for="complete" class="control-label">{lang t="system|export_type"}</label>
					<div class="controls">
						<div class="btn-group" data-toggle="radio">
{foreach $export_type as $row}
							<input type="radio" name="export_type" id="{$row.value}" value="{$row.value}"{$row.checked}>
							<label for="{$row.value}" class="btn">{$row.lang}</label>
{/foreach}
						</div>
					</div>
				</div>
				<div id="options-container" class="control-group">
					<label for="drop" class="control-label">{lang t="system|options"}</label>
					<div class="controls">
						<label for="drop" class="checkbox">
							<input type="checkbox" name="drop" id="drop" value="1"{$drop.checked}>
							{$drop.lang}
						</label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="form-actions">
		<button type="submit" name="submit" class="btn">{lang t="system|submit"}</button>
		{$form_token}
	</div>
</form>
{/if}
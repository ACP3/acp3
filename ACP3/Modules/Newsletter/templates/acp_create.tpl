{if isset($error_msg)}
{$error_msg}
{/if}
{include_js module="newsletter" file="acp"}
<form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8" class="form-horizontal">
	<div class="control-group">
		<label for="title" class="control-label">{lang t="newsletter|subject"}</label>
		<div class="controls"><input type="text" name="title" id="title" value="{$form.title}" required></div>
	</div>
	<div class="control-group">
		<label for="text" class="control-label">{lang t="newsletter|text"}</label>
		<div class="controls"><textarea name="text" id="text" cols="50" rows="5" class="span6" required>{$form.text}</textarea></div>
	</div>
	<div class="control-group">
		<label for="action-1" class="control-label">{lang t="newsletter|action"}</label>
		<div class="controls">
			<div class="btn-group" data-toggle="radio">
{foreach $action as $row}
				<input type="radio" name="action" id="action-{$row.value}" value="{$row.value}"{$row.checked}>
				<label for="action-{$row.value}" class="btn">{$row.lang}</label>
{/foreach}
			</div>
		</div>
	</div>
	<div id="test-newsletter" class="control-group">
		<label for="test-1" class="control-label">{lang t="newsletter|test_newsletter"}</label>
		<div class="controls">
			<div class="btn-group" data-toggle="radio">
{foreach $test as $row}
				<input type="radio" name="test" id="test-{$row.value}" value="{$row.value}"{$row.checked}>
				<label for="test-{$row.value}" class="btn">{$row.lang}</label>
{/foreach}
			</div>
			<p class="help-block">{lang t="newsletter|test_nl_description"}</p>
		</div>
	</div>
	<div class="form-actions">
		<button type="submit" name="submit" class="btn">{lang t="system|submit"}</button>
		<a href="{uri args="acp/newsletter"}" class="btn">{lang t="system|cancel"}</a>
		{$form_token}
	</div>
</form>
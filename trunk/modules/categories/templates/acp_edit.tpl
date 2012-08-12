{if isset($error_msg)}
{$error_msg}
{/if}
<form action="{$REQUEST_URI}" method="post" enctype="multipart/form-data" accept-charset="UTF-8" class="form-horizontal">
	<fieldset>
		<legend>{lang t="categories|acp_edit"}</legend>
		<div class="control-group">
			<label for="name" class="control-label">{lang t="common|name"}</label>
			<div class="controls"><input type="text" name="name" id="name" value="{$form.name}" maxlength="120"></div>
		</div>
		<div class="control-group">
			<label for="description" class="control-label">{lang t="common|description"}</label>
			<div class="controls"><input type="text" name="description" id="description" value="{$form.description}" maxlength="120"></div>
		</div>
		<div class="control-group">
			<label for="picture" class="control-label">{lang t="categories|picture"}</label>
			<div class="controls"><input type="file" id="picture" name="picture" value=""></div>
		</div>
	</fieldset>
	<div class="form-actions">
		<input type="submit" name="submit" value="{lang t="common|submit"}" class="btn">
		{$form_token}
	</div>
</form>
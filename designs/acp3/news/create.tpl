{if isset($error_msg)}
{$error_msg}
{/if}
<form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8">
	<div id="tabs">
		<ul>
			<li><a href="#tab-1">{lang t="common|publication_period"}</a></li>
			<li><a href="#tab-2">{lang t="news|news"}</a></li>
			<li><a href="#tab-3">{lang t="news|hyperlink"}</a></li>
			<li><a href="#tab-4">{lang t="common|seo"}</a></li>
		</ul>
		<div id="tab-1">
			<dl>
				<dt><label for="start">{lang t="common|publication_period"}</label></dt>
				<dd>{$publication_period}</dd>
			</dl>
			<p>
				{lang t="common|date_description"}
			</p>
		</div>
		<div id="tab-2" class="ui-tabs-hide">
			<dl>
				<dt><label for="headline">{lang t="news|headline"}</label></dt>
				<dd><input type="text" name="headline" id="headline" value="{$form.headline}" maxlength="120"></dd>
				<dt><label for="text">{lang t="news|text"}</label></dt>
				<dd>{wysiwyg name="text" value="`$form.text`" height="250"}</dd>
				<dt><label for="cat">{lang t="common|category"}</label></dt>
				<dd>
					{$categories}
				</dd>
{if isset($options)}
				<dt><label for="{$options.0.name}">{lang t="common|options"}</label></dt>
				<dd>
					<ul style="margin:0 20px;list-style:none">
{foreach $options as $row}
						<li>
							<label for="{$row.name}">
								<input type="checkbox" name="{$row.name}" id="{$row.name}" value="1" class="checkbox"{$row.checked}>
								{$row.lang}
							</label>
						</li>
{/foreach}
					</ul>
				</dd>
{/if}
			</dl>
		</div>
		<div id="tab-3" class="ui-tabs-hide">
			<dl>
				<dt><label for="link-title">{lang t="news|link_title"}</label></dt>
				<dd><input type="text" name="link_title" id="link-title" value="{$form.link_title}" maxlength="120"></dd>
				<dt><label for="uri">{lang t="news|uri"}</label></dt>
				<dd><input type="url" name="uri" id="uri" value="{$form.uri}" maxlength="120"></dd>
				<dt><label for="target">{lang t="news|target_page"}</label></dt>
				<dd>
					<select name="target" id="target">
{foreach $target as $row}
						<option value="{$row.value}"{$row.selected}>{$row.lang}</option>
{/foreach}
					</select>
				</dd>
			</dl>
		</div>
		<div id="tab-4" class="ui-tabs-hide">
			{$SEO_FORM_FIELDS}
		</div>
	</div>
	<div class="form-bottom">
		<input type="submit" name="submit" value="{lang t="common|submit"}" class="form">
		<input type="reset" value="{lang t="common|reset"}" class="form">
		{$form_token}
	</div>
</form>
{if isset($export)}
    <pre>
		{$export}
	</pre>
{else}
    {if isset($error_msg)}
        {$error_msg}
    {/if}
    <form action="{$REQUEST_URI}" method="post" accept-charset="UTF-8" class="form-horizontal ajax-form">
        <div class="form-group">
            <label for="tables" class="col-lg-2 control-label">{lang t="system|sql_tables"}</label>

            <div class="col-lg-10">
                <select class="form-control" name="tables[]" id="tables" multiple="multiple" style="height:200px">
                    {foreach $tables as $row}
                        <option value="{$row.name}"{$row.selected}>{$row.name}</option>
                    {/foreach}
                </select>

                <p class="help-block">
                    <a href="javascript:mark_options('add')">{lang t="system|mark_all"}</a> <span>::</span>
                    <a href="javascript:mark_options('remove')">{lang t="system|unmark_all"}</a>
                </p>
            </div>
        </div>
        <div class="form-group">
            <label for="{$output.0.id}" class="col-lg-2 control-label">{lang t="system|output"}</label>

            <div class="col-lg-10">
                <div class="btn-group" data-toggle="buttons">
                    {foreach $output as $row}
                        <label for="{$row.id}" class="btn btn-default{if !empty($row.checked)} active{/if}">
                            <input type="radio" name="output" id="{$row.id}" value="{$row.value}"{$row.checked}>
                            {$row.lang}
                        </label>
                    {/foreach}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="{$export_type.0.id}" class="col-lg-2 control-label">{lang t="system|export_type"}</label>

            <div class="col-lg-10">
                <div class="btn-group" data-toggle="buttons">
                    {foreach $export_type as $row}
                        <label for="{$row.id}" class="btn btn-default{if !empty($row.checked)} active{/if}">
                            <input type="radio" name="export_type" id="{$row.id}" value="{$row.value}"{$row.checked}>
                            {$row.lang}
                        </label>
                    {/foreach}
                </div>
            </div>
        </div>
        <div id="options-container" class="form-group">
            <label for="drop" class="col-lg-2 control-label">{lang t="system|options"}</label>

            <div class="col-lg-10">
                <div class="checkbox">
                    <label for="drop"> <input type="checkbox" name="drop" id="drop" value="1"{$drop.checked}>
                        {$drop.lang}
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
                <button type="submit" name="submit" class="btn btn-primary">{lang t="system|submit"}</button>
                {$form_token}
            </div>
        </div>
    </form>
    {include_js module="system" file="acp_sql_export"}
    {include_js module="system" file="forms"}
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.ajax-form').formSubmit('{lang t="system|loading_please_wait"}');
        });
    </script>
{/if}
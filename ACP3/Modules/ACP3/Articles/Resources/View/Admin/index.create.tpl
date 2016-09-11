{extends file="asset:System/layout.ajax-form.tpl"}

{block AJAX_FORM_CONTENT}
    <div class="tabbable">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-1" data-toggle="tab">{lang t="system|publication_period"}</a></li>
            <li><a href="#tab-2" data-toggle="tab">{lang t="articles|page_statements"}</a></li>
            <li><a href="#tab-3" data-toggle="tab">{lang t="seo|seo"}</a></li>
        </ul>
        <div class="tab-content">
            <div id="tab-1" class="tab-pane fade in active">
                {datepicker name=['start', 'end'] value=[$form.start, $form.end]}
            </div>
            <div id="tab-2" class="tab-pane fade">
                <div class="form-group">
                    <label for="title" class="col-sm-2 control-label required">{lang t="articles|title"}</label>

                    <div class="col-sm-10">
                        <input class="form-control"
                               type="text"
                               name="title"
                               id="title"
                               value="{$form.title}"
                               maxlength="120"
                               data-seo-slug-base="true"
                               required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="text" class="col-sm-2 control-label required">{lang t="articles|text"}</label>

                    <div class="col-sm-10">{wysiwyg name="text" value="`$form.text`" height="250" advanced="1"}</div>
                </div>
                {if !empty($options)}
                    {include file="asset:System/Partials/form_group.checkbox.tpl" label={lang t="system|options"}}
                    {include file="asset:Menus/Partials/create_menu_item.tpl"}
                {/if}
            </div>
            <div id="tab-3" class="tab-pane fade">
                {include file="asset:Seo/Partials/seo_fields.tpl" seo=$SEO_FORM_FIELDS}
            </div>
        </div>
    </div>
    {include file="asset:System/Partials/form_group.submit.tpl" form_token=$form_token back_url={uri args="acp/articles"}}
    {javascripts}
        {include_js module="articles" file="admin/acp"}
    {/javascripts}
{/block}

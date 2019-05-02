{event name="core.wysiwyg.`$wysiwyg.friendly_name|lower`.before" id=$wysiwyg.id}
<textarea name="{$wysiwyg.name}" id="{$wysiwyg.id}" cols="60" rows="6" class="form-control">{$wysiwyg.value|escape:'html'}</textarea>
{javascripts}
    {include file="asset:System/Partials/wysiwyg_config.tpl" js=$wysiwyg.js}
{/javascripts}
{if $wysiwyg.advanced === true}
    <div id="page-break-form" class="modal fade" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{lang t="system|insert_page_break"}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <label for="toc-title">{lang t="system|title_for_toc"}</label>
                    <input type="text" id="toc-title" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{lang t="system|close"}</button>
                    <button class="btn btn-primary">{lang t="system|submit"}</button>
                </div>
            </div>
        </div>
    </div>
    <div id="page-break-link" class="align-left">
        <a href="#" class="btn btn-light" data-toggle="modal" data-target="#page-break-form">
            {lang t="system|insert_page_break"}
        </a>
    </div>
    {javascripts}
        <script>
            var wysiwygCallback = function(text) {
                {$wysiwyg.advanced_replace_content}
            };
        </script>
        {include_js module="system" file="partials/wysiwyg" depends="bootstrap"}
    {/javascripts}
{/if}

{include_js module="emoticons" file="emoticons"}
<div class="icons" data-emoticons-input="#{$emoticons_field_id}">
    {foreach $emoticons as $key => $value}
        <a href="#" title="{$key}">{$value}</a>
    {/foreach}
</div>
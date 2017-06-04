{extends file="asset:System/layout.admin-grid.tpl"}

{$DELETE_ROUTE={uri args="acp/menus/items/delete"}}

{block HEADER_BAR_OPTIONS}
    {check_access mode="link" path="acp/menus/items/create" class="fa fa-plus text-success"}
    {check_access mode="link" path="acp/menus/index/create" class="fa fa-th text-info"}
    {if isset($pages_list)}
        {check_access mode="button" path="acp/menus/items/delete" class="fa fa-trash text-danger" lang="system|delete_marked"}
    {/if}
{/block}
{block ADMIN_GRID_CONTENT}
    {if isset($pages_list)}
        <div class="table-responsive">
            <table class="table table-striped table-hover datagrid">
                <thead>
                <tr>
                    {if $can_delete_item === true}
                        <th class="datagrid-column datagrid-column__mass-action">
                            <input type="checkbox" id="mark-all" value="1" {mark name="entries"}>
                        </th>
                    {/if}
                    <th style="width:30%">{lang t="menus|title"}</th>
                    <th>{lang t="menus|page_type"}</th>
                    {if $can_order_item === true}
                        <th>{lang t="system|order"}</th>
                    {/if}
                    <th style="width:5%">{lang t="system|id"}</th>
                    {if $can_delete_item === true || $can_edit_item === true}
                        <th class="datagrid-column datagrid-column__actions">{lang t="system|action"}</th>
                    {/if}
                </tr>
                </thead>
                <tbody>
                {foreach $pages_list as $block => $values}
                    <tr>
                        <td class="sub-table-header{if $can_edit || $can_delete} has-buttons{/if}" colspan="{$colspan}">
                            {$values.title} <span>({lang t="menus|index_name2"} {$block})</span>
                            {if $can_delete || $can_edit}
                                <div class="btn-group pull-right">
                                    {if $can_edit}
                                        <a href="{uri args="acp/menus/index/edit/id_`$values.menu_id`"}" class="btn btn-default btn-sm" title="{lang t="menus|admin_index_edit"}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    {/if}
                                    {if $can_delete}
                                        <a href="{uri args="acp/menus/index/delete/entries_`$values.menu_id`"}" class="btn btn-danger btn-sm" title="{lang t="menus|admin_index_delete"}" data-ajax-form="true" data-ajax-form-loading-text="{lang t="system|loading_please_wait"}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    {/if}
                                </div>
                            {/if}
                        </td>
                    </tr>
                    {foreach $values.items as $row}
                        <tr>
                            {if $can_delete_item === true}
                                <td class="datagrid-column datagrid-column__mass-action">
                                    <input type="checkbox" name="entries[]" value="{$row.id}">
                                </td>
                            {/if}
                            <td>{$row.spaces}{$row.title}</td>
                            <td>{$row.mode_formatted}</td>
                            {if $can_order_item === true}
                                <td>
                                    {if !$row.last}
                                        <a href="{uri args="acp/menus/items/order/id_`$row.id`/action_down"}"
                                           title="{lang t="system|move_down"}"
                                           data-ajax-form="true"
                                           data-ajax-form-loading-text="{lang t="system|loading_please_wait"}"><i class="fa fa-arrow-down" aria-hidden="true"></i></a>
                                    {/if}
                                    {if !$row.first}
                                        <a href="{uri args="acp/menus/items/order/id_`$row.id`/action_up"}"
                                           title="{lang t="system|move_up"}"
                                           data-ajax-form="true"
                                           data-ajax-form-loading-text="{lang t="system|loading_please_wait"}"><i class="fa fa-arrow-up" aria-hidden="true"></i></a>
                                    {/if}
                                    {if $row.first && $row.last}
                                        <i class="fa fa-minus-circle text-danger" aria-hidden="true" title="{lang t="system|move_impossible"}"></i>
                                    {/if}
                                </td>
                            {/if}
                            <td>{$row.id}</td>
                            {if $can_delete_item === true || $can_edit_item === true}
                                <td class="datagrid-column datagrid-column__actions">
                                    <div class="btn-group pull-right">
                                        {if $can_edit_item === true}
                                            <a href="{uri args="acp/menus/items/edit/id_`$row.id`"}" class="btn btn-default btn-xs" title="{lang t="menus|admin_items_edit"}">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                        {/if}
                                        {if $can_delete_item === true}
                                            <a href="{uri args="acp/menus/items/delete/entries_`$row.id`"}" class="btn btn-danger btn-xs" title="{lang t="menus|admin_items_delete"}">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        {/if}
                                    </div>
                                </td>
                            {/if}
                        </tr>
                    {/foreach}
                {/foreach}
                </tbody>
            </table>
        </div>
        {if isset($pages_list)}
            {javascripts}
            {include_js module="system" file="ajax-form"}
            {/javascripts}
            {if $can_delete === true}
                {include file="asset:System/Partials/mark.tpl"}
            {/if}
        {/if}
    {else}
        {include file="asset:System/Partials/no_results.tpl"}
    {/if}
{/block}

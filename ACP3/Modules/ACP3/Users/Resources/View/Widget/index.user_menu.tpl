<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{lang t="users|user_menu"}</h3>
    </div>
    <div class="list-group">
        <a href="{uri args="users/account"}" class="list-group-item">
            <i class="glyphicon glyphicon-home"></i>
            {lang t="users|home"}
        </a>
        {if !empty($modules)}
            <div id="menu-administration" class="list-group-item dropdown">
                <a href="{uri args="acp/acp"}" id="menu-admin-label" class="dropdown-toggle" data-toggle="dropdown" data-target="#menu-administration">
                    <i class="glyphicon glyphicon-file"></i>
                    {lang t="users|administration"}
                    <b class="caret"></b>
                </a>
                <ul class="dropdown-menu" role="menu" aria-labelledby="menu-admin-label">
                    {foreach $modules as $row}
                        <li><a href="{uri args="acp/`$row.path`"}">{$row.name}</a></li>
                    {/foreach}
                </ul>
            </div>
        {/if}
        {if !empty($system)}
            <div id="menu-system" class="list-group-item dropdown">
                <a href="{uri args="acp/system"}" id="menu-system-label" class="dropdown-toggle" data-toggle="dropdown" data-target="#menu-system">
                    <i class="glyphicon glyphicon-cog"></i>
                    {lang t="system|system"}
                    <b class="caret"></b>
                </a>
                <ul class="dropdown-menu" role="menu" aria-labelledby="menu-system-label">
                    {foreach $system as $row}
                        <li><a href="{uri args="acp/`$row.path`"}">{$row.name}</a></li>
                    {/foreach}
                </ul>
            </div>
        {/if}
        <a href="{uri args="users/index/logout"}" class="list-group-item">
            <i class="glyphicon glyphicon-off"></i>
            {lang t="users|logout"}
        </a>
    </div>
</div>

<?php
namespace ACP3\Core\View\Renderer\Smarty\Functions;

use ACP3\Core;

/**
 * Class HasPermission
 * @package ACP3\Core\View\Renderer\Smarty\Functions
 */
class HasPermission extends AbstractFunction
{
    /**
     * @var Core\ACL
     */
    protected $acl;

    /**
     * @param Core\ACL $acl
     */
    public function __construct(Core\ACL $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @inheritdoc
     */
    public function getPluginName()
    {
        return 'has_permission';
    }

    /**
     * @inheritdoc
     */
    public function process(array $params, \Smarty_Internal_Template $smarty)
    {
        if (isset($params['path']) === true) {
            return $this->acl->hasPermission($params['path']);
        } else {
            return false;
        }
    }
}

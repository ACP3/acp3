<?php
namespace ACP3\Core\View\Renderer\Smarty\Functions;

use ACP3\Core;

class HasPermission extends AbstractFunction
{
    /**
     * @var Core\ACL\ACLInterface
     */
    private $acl;

    /**
     * HasPermission constructor.
     * @param Core\ACL\ACLInterface $acl
     */
    public function __construct(Core\ACL\ACLInterface $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionName()
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
        }

        return false;
    }
}

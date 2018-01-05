<?php
namespace ACP3\Core\Validation\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterValidationRulesPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('core.validator');
        $plugins = $container->findTaggedServiceIds('core.validation.validation_rule');

        foreach ($plugins as $serviceId => $tags) {
            $definition->addMethodCall('registerValidationRule', [new Reference($serviceId)]);
        }
    }
}

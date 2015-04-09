<?php

namespace Binidini\CoreBundle;

use Binidini\CoreBundle\DependencyInjection\Compiler\FOSUserOverridePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BinidiniCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new FOSUserOverridePass());
    }
}

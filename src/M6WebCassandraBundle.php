<?php
namespace M6Web\Bundle\CassandraBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class M6WebCassandraBundle
 */
class M6WebCassandraBundle extends Bundle
{
    /**
     * @return DependencyInjection\M6WebCassandraExtension|null|\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new DependencyInjection\M6WebCassandraExtension();
    }
}

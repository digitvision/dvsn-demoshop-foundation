<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DvsnDemoshopFoundation extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        $container->setParameter('dvsn.demoshop_foundation.path', $this->getPath());
        parent::build($container);
    }

    public function activate(ActivateContext $activateContext): void
    {
    }

    public function install(InstallContext $installContext): void
    {
        $installer = new Setup\Install(
            $installContext,
            $this->container->get(Connection::class),
            $this->container
        );
        $installer->install();

        $installer = new Setup\Update(
            $installContext,
            $this->container->get(Connection::class),
            $this->container->get('custom_field_set.repository'),
            $this->container->get('custom_field.repository')
        );
        $installer->install();
    }

    public function postInstall(InstallContext $installContext): void
    {
    }

    public function update(UpdateContext $updateContext): void
    {
        $installer = new Setup\Update(
            $updateContext,
            $this->container->get(Connection::class),
            $this->container->get('custom_field_set.repository'),
            $this->container->get('custom_field.repository')
        );
        $installer->update($updateContext->getCurrentPluginVersion());
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        $installer = new Setup\Uninstall(
            $uninstallContext,
            $this->container->get(Connection::class),
            $this->container->get('custom_field_set.repository')
        );
        $installer->uninstall();
    }
}

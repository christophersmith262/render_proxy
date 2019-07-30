<?php

namespace Drupal\render_proxy;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Replaces the core renderer service with the renderer proxy.
 */
class RenderProxyServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $core_definition = $container->getDefinition('renderer');

    $proxy_definition = new Definition('\Drupal\render_proxy\Render\RendererProxy', [
      new Reference('renderer.original'),
      new Reference('plugin.manager.render_proxy.filter'),
    ]);

    $container->addDefinitions([
      'renderer.original' => $core_definition,
      'renderer' => $proxy_definition,
    ]);
  }

}

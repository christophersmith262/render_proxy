<?php

namespace Drupal\render_proxy\Plugin;

use Drupal\Component\Plugin\Factory\DefaultFactory;

/**
 * Creates render proxy plugins.
 */
class RenderProxyFilterFactory extends DefaultFactory {

  /** 
   * {@inheritdoc}
   */  
  public function createInstance($plugin_id, array $configuration = []) {
    $plugin_definition = $this->discovery->getDefinition($plugin_id);
    $plugin_class = static::getPluginClass($plugin_id, $plugin_definition, $this->interface);

    // If the plugin provides a factory method, pass the container to it.
    if (is_subclass_of($plugin_class, 'Drupal\Core\Plugin\ContainerFactoryPluginInterface')) {
      return $plugin_class::create(\Drupal::getContainer(), $configuration, $plugin_id, $plugin_definition);
    }   

    // Otherwise, create the plugin directly.
    return new $plugin_class($configuration, $plugin_id, $plugin_definition);
  }

}

<?php

namespace Drupal\render_proxy\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Represents the plugin manager for render proxy filters.
 */
interface RenderProxyFilterManagerInterface extends PluginManagerInterface {

  /**
   * Gets the active render proxy filter chain.
   *
   * @return \Drupal\render_proxy\Plugin\RenderProxyFilterInterface[]
   *   The list of active filters to be applied.
   */
  public function getActiveFilters();

}

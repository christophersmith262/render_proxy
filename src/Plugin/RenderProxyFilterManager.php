<?php

namespace Drupal\render_proxy\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a plugin manager for render proxy filters.
 */
class RenderProxyFilterManager extends DefaultPluginManager implements RenderProxyFilterManagerInterface {

  /** 
   * {@inheritdoc}
   */  
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/render_proxy/filter', $namespaces, $module_handler, 'Drupal\render_proxy\Plugin\RenderProxyFilterInterface', 'Drupal\render_proxy\Annotation\RenderProxyFilter');
    $this->alterInfo('render_proxy_filter_info');
    $this->setCacheBackend($cache_backend, "render_proxy_filter_info_plugins");
    $this->factory = new RenderProxyFilterFactory($this->getDiscovery());
  }

  /**
   * {@inherit}
   */
  public function getActiveFilters() {
    return [];
  }

}

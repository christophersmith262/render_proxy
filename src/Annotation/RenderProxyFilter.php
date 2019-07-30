<?php

namespace Drupal\render_proxy\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a render proxy for processing render calls.
 *
 * Plugin Namespace: Plugin\render_proxy\filter
 *
 * @Annotation
 */
class RenderProxyFilter extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}

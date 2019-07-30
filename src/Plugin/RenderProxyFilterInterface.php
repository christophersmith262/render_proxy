<?php

namespace Drupal\render_proxy\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Represents a plugin for altering rendered output of all render calls.
 */
interface RenderProxyFilterInterface extends ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Check the render array before rendering to see if the chain should halt.
   *
   * @param array $elements
   *   The render array to examine.
   *
   * @return bool
   *   FALSE to halt, or non-FALSE to continue.
   */
  public function checkElements(array $elements);

  /**
   * Check the generated markup to see if the chain should halt.
   *
   * @param string $markup
   *   The markup to examine.
   *
   * @return bool
   *   FALSE to halt, or non-FALSE to continue.
   */
  public function checkMarkup($markup);

  /**
   * Processes a render array before it gets rendered.
   *
   * @param array &$elements
   *   The render array that will be rendered.
   *
   * @return null|FALSE
   */
  public function preProcess(array &$elements);

  /**
   * Processes rendered markup.
   *
   * @param string $markup
   *   The markup to be altered.
   *
   * @return string
   *   The altered markup.
   */
  public function postProcess($markup);

}

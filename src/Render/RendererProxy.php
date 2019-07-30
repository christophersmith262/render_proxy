<?php

namespace Drupal\render_proxy\Render;

use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\render_proxy\Plugin\RenderProxyFilterManagerInterface;

/**
 * A proxy for the core Drupal renderer service that allows catch-all filtering.
 *
 * This proxy adds a post_render callback to each top-level render call that
 * will execute the active RenderProxyFilterPlugin stack.
 *
 * This is implemented as a proxy instead of a direct subclassso that we avoid
 * having to deeply integrate with Drupal's render caching system, and instead
 * just rely on the built-in behavior.
 */
class RendererProxy implements RendererInterface {

  /**
   * The core renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $originalRenderer;

  /**
   * The render proxy filter plugin manager.
   *
   * @var \Drupal\render_proxy\Plugin\RenderProxyFilterManagerInterface
   */
  protected $filterManager;

  /**
   * Creates the renderer proxy service.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The core renderer service.
   * @param \Drupal\render_proxy\Plugin\RenderProxyFilterManagerInterface $filter_manager
   *   The render proxy filter plugin manager service.
   */
  public function __construct(RendererInterface $renderer, RenderProxyFilterManagerInterface $filter_manager) {
    $this->originalRenderer = $renderer;
    $this->filterManager = $filter_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function addCacheableDependency(array &$elements, $dependency) {
    return $this->originalRenderer->addCacheableDependency($elements, $dependency);
  }

  /**
   * {@inheritdoc}
   */
  public function executeInRenderContext(RenderContext $context, callable $callable) {
    return $this->originalRenderer->executeInRenderContext($context, $callable);
  }

  /**
   * {@inheritdoc}
   */
  public function hasRenderContext() {
    return $this->originalRenderer->hasRenderContext();
  }

  /**
   * {@inheritdoc}
   */
  public function mergeBubbleableMetadata(array $a, array $b) {
    return $this->originalRenderer->mergeBubbleableMetadata($a, $b);
  }

  /**
   * {@inheritdoc}
   */
  public function render(&$elements, $is_root_call = FALSE) {
    $this->attachRenderProxy($elements);
    return $this->originalRenderer->render($elements, $is_root_call);
  }

  /**
   * {@inheritdoc}
   */
  public function renderPlaceholder($placeholder, array $elements) {
    $this->attachRenderProxy($elements);
    return $this->originalRenderer->renderPlaceholder($placeholder, $elements);
  }

  /**
   * {@inheritdoc}
   */
  public function renderPlain(&$elements) {
    $this->attachRenderProxy($elements);
    return $this->originalRenderer->renderPlain($elements);
  }

  /**
   * {@inheritdoc}
   */
  public function renderRoot(&$elements) {
    $this->attachRenderProxy($elements);
    return $this->originalRenderer->renderRoot($elements);
  }

  /**
   * Tells the core render service to run the render result through the proxy.
   *
   * @param array &$elements
   *   The render array to add the proxy service to.
   */
  protected function attachRenderProxy(&$elements) {
    $filters = $this->filterManager->getActiveFilters();
    foreach ($filters as $filter) {
      if ($filter->checkElements($elements) === FALSE) {
        return;
      }
      $filter->preProcess($elements);
    }
    if (!array_key_exists('#lazy_builder', $elements)) {
      $elements['#post_render'][] = [$this, 'proxyRenderResult'];
    }
  }

  /**
   * Applies the active render proxy plugins.
   *
   * @param string $markup
   *   The generated markup to be altered.
   *
   * @return string
   *   The final markup to be sent to the browser / cached.
   */
  public function proxyRenderResult($markup) {
    $filters = $this->filterManager->getActiveFilters();
    foreach ($filters as $filter) {
      if ($filter->checkMarkup($markup) === FALSE) {
        return;
      }
      $markup = $filter->postProcess($markup);
    }
    return $markup;
  }

}

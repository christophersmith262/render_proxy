<?php

namespace Drupal\render_proxy\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A base class for render proxy filters.
 */
abstract class RenderProxyFilterBase extends PluginBase implements RenderProxyFilterInterface {

  /**
   * {@inheritdocs}
   */
  public function checkElements(array $elements) {
    return TRUE;
  }

  /**
   * {@inheritdocs}
   */
  public function checkMarkup($markup) {
    return TRUE;
  }

  /**
   * {@inheritdocs}
   */
  public function preProcess(array &$elements) {
  }

  /**
   * {@inheritdocs}
   */
  public function postProcess($markup) {
    return $markup;
  }

  /**
   * {@inheritdocs}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdocs}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdocs}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdocs}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdocs}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdocs}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

}

<?php

namespace Drupal\render_proxy\Plugin\render_proxy\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\render_proxy\Plugin\RenderProxyFilterBase;

/**
 * Filters rendered html by a regex replacement pattern.
 *
 * @RenderProxyFilter(
 *   id = "theme_condition",
 *   title = @Translation("Condition: Active Theme"),
 *   description = @Translation("Halt the processing chain if the selected theme is not active.")
 * )
 */
class ThemeCondition extends RenderProxyFilterBase {

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['theme'] = [
      '#type' => 'textfield',
      '#title' => t('Theme'),
      '#description' => t('Halt the processing chain unless this theme is active.'),
    ];

    return $form;
  }

}

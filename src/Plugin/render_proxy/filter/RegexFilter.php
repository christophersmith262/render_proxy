<?php

namespace Drupal\render_proxy\Plugin\render_proxy\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\render_proxy\Plugin\RenderProxyFilterBase;

/**
 * Filters rendered html by a regex replacement pattern.
 *
 * @RenderProxyFilter(
 *   id = "regex",
 *   title = @Translation("Regular Expression"),
 *   description = @Translation("Replace a regex pattern in rendered markup.")
 * )
 */
class RegexFilter extends RenderProxyFilterBase {

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['pattern'] = [
      '#type' => 'textfield',
      '#title' => t('Regex Pattern'),
      '#description' => t('This pattern will be passed directly to regex_replace.'),
    ];

    $form['replacement'] = [
      '#type' => 'textfield',
      '#title' => t('Replacement'),
      '#description' => t('Can contain references to the original pattern.'),
    ];

    return $form;
  }

}

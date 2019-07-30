<?php

namespace Drupal\render_proxy\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\render_proxy\Plugin\RenderProxyFilterManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RenderProxySettingsForm extends ConfigFormBase {

  /**
   * The filter plugin manager service.
   *
   * @var \Drupal\render_proxy\Plugin\RenderProxyFilterManagerInterface
   */
  protected $filterManager;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\render_proxy\Plugin\RenderProxyFilterManagerInterface $filter_manager
   *   The filter plugin manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RenderProxyFilterManagerInterface $filter_manager) {
    $this->setConfigFactory($config_factory);
    $this->filterManager = $filter_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.render_proxy.filter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'render_proxy_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'render_proxy.filter.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('render_proxy.filter.settings');

    $filters_wrapper = Html::getUniqueId('filters');

    $form['filters'] = [
      '#prefix' => '<div id="' . $filters_wrapper . '">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
      'description' => [
        '#markup' => $this->t('<p>The filter chain below will be applied each time the Drupal <i>renderer</i> service is executed. You can drag and drop the filters here to control the order of execution.</p>'),
      ],
    ];
      
    $form['filters']['active'] = [
      '#type' => 'table',
      '#header' => ['', $this->t('Type'), $this->t('Configuration'), $this->t('Actions'), $this->t('Weight')],
      '#empty' => $this->t('There are no active filters to display.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ],
    ];

    $filters = $form_state->get('filters');
    $form_state->set('filters', NULL);
    if (is_null($filters)) {
      $filters = $form_state->getValue(['filters', 'active']);
    }
    if (is_null($filters)) {
      $filters = $config->get('filters');
    }
    if (!$filters) {
      $filters = [];
    }

    $filter_options = [];
    $filter_definitions = $this->filterManager->getDefinitions();
    foreach ($filter_definitions as $plugin_id => $plugin_definition) {
      $filter_options[$plugin_id] = $plugin_definition['title'];
    }

    foreach ($filters as $id => $filter) {
      $this->decoratePluginData($filter, $filter_definitions, $form_state);

      $form['filters']['active'][$id] = [
        '#weight' => $filter['weight'],
        '#attributes' => [
          'class' => [
            'draggable',
          ],
        ],
        'placeholder' => [],
        'type' => [
          '#type' => 'select',
          '#title' => $this->t('Plugin'),
          '#options' => $filter_options,
          '#empty_option' => $this->t('- Select a Filter Type -'),
          '#default_value' => $filter['type'],
          '#description' => $filter['description'],
          '#ajax' => [
            'callback' => [$this, 'deliverFilters'],
            'wrapper' => $filters_wrapper,
            'effect' => 'fade',
          ],
        ],
        'config' => $filter['plugin_form'],
        'actions' => [
          'remove' => [
            '#type' => 'submit',
            '#name' => 'remove-' . $id,
            '#value' => $this->t('Remove Filter'),
            '#submit' => [[$this, 'removeFilterSubmit']],
            '#ajax' => [
              'callback' => [$this, 'deliverFilters'],
              'wrapper' => $filters_wrapper,
              'effect' => 'fade',
            ],
            '#weight' => 999,
            '#filter_id' => $id,
          ],
        ],
        'weight' => [
          '#type' => 'weight',
          '#title' => $this->t('Weight'),
          '#title_display' => 'invisible',
          '#default_value' => $filter['weight'],
          '#attributes' => [
            'class' => [
              'table-sort-weight',
            ],
          ],
        ],
      ];
    }

    $form['filters']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Filter'),
      '#submit' => [[$this, 'addFilterSubmit']],
      '#ajax' => [
        'callback' => [$this, 'deliverFilters'],
        'wrapper' => $filters_wrapper,
        'effect' => 'fade',
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */  
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('render_proxy.filter.settings')
      ->set('filters', $this->getSubmittedFilters($form_state))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Handles click on the 'add filter' button.
   *
   * @param array $form
   *   The form the button was rendered on.
   * @param \Drupal\Form\FormStateInterface $form_state
   *   The form state of the form being submitted.
   */
  public function addFilterSubmit(array $form, FormStateInterface $form_state) {
    $submitted_values = $this->getSubmittedFilters($form_state);

    $i = 0;
    $filters = [];
    foreach ($submitted_values as $filter) {
      $filter['weight'] = $i;
      $filters[$i] = $filter;
      $i++;
    }

    $filters[$i] = [
      'type' => NULL,
      'config' => [],
      'weight' => $i,
    ];

    $form_state->set('filters', $filters);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Handles clicks on the 'remove filter' button.
   *
   * @param array $form
   *   The form the button was rendered on.
   * @param \Drupal\Form\FormStateInterface $form_state
   *   The form state of the form being submitted.
   */
  public function removeFilterSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $id = $button['#filter_id'];
    $filters = $this->getSubmittedFilters($form_state);
    unset($filters[$id]);
    $form_state->set('filters', $filters);
    $form_state->setRebuild(TRUE);
  }

  /**
   * The ajax callback for updating the form.
   *
   * @param array &$form
   *   The form the button was rendered on.
   * @param \Drupal\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The updated render array.
   */
  public function deliverFilters(array &$form, FormStateInterface $form_state) {
    return $form['filters'];
  }

  /**
   * Gets the submitted active filter list.
   *
   * @param \Drupal\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   A map of sanitized active filter submitted data.
   */
  protected function getSubmittedFilters(FormStateInterface $form_state) {
    $filters = $form_state->getValue(['filters', 'active']);
    if (!$filters) {
      $filters = [];
    }

    foreach ($filters as &$filter) {
      unset($filter['actions']);
    }

    return $filters;
  }

  /**
   * Fills in fields on the filter form values based on the referenced plugin.
   *
   * @param array &$filter
   *   The filter array to be expanded.
   * @param array $filter_definitions
   *   An array of filter definitions obtained from the filter manager service.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form being assembled.
   */
  protected function decoratePluginData(array &$filter, array $filter_definitions, FormStateInterface $form_state) {
    $filter_definitions = $this->filterManager->getDefinitions();

    if (empty($filter['type'])) {
      $filter['type'] = NULL;
    }

    if (empty($filter['config'])) {
      $filter['config'] = [];
    }

    if (!empty($filter_definitions[$filter['type']])) {
      $filter_definition = $filter_definitions[$filter['type']];
      $plugin_instance = $this->filterManager->createInstance($filter['type'], $filter['config']);
      $filter['plugin_form'] = $plugin_instance->buildConfigurationForm([], $form_state);
      $filter['description'] = $filter_definition['description'];
    }
    else {
      $filter['plugin_form'] = [];
      $filter['description'] = NULL;
    }
  }

}

<?php

namespace Drupal\courses\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\courses\Service\CourseManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays a list of featured developers.
 */
#[Block(
  id: 'course_featured',
  admin_label: new TranslatableMarkup('Cursos destacados'),
)]
class FeaturedDevelopersBlock extends BlockBase implements ContainerFactoryPluginInterface {
  
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected CourseManager $courseManager,) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition,): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('course.manager'),
    );
  }

  public function defaultConfiguration(): array {
    return [
        'number_of_course' => 5,
    ] + parent::defaultConfiguration();
  }

  public function blockForm($form, FormStateInterface $form_state,): array {
    $form = parent::blockForm($form, $form_state);

    $form['number_of_course'] = [
      '#type' => 'number',
      '#title' => $this->t('Número de cursos'),
      '#default_value' => $this->configuration['number_of_course'],
      '#min' => 1,
      '#max' => 20,
      '#required' => TRUE,
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);

    $this->configuration['number_of_course'] = (int) $form_state->getValue('number_of_course');
  }

  public function build(): array {
    $courses = $this->courseManager->getCourses($this->configuration['number_of_course']);

    $items = [];

    foreach ($courses as $course) {
      $items[] = [
        '#type' => 'link',
        '#title' => $course->getTitle(),
        '#url' => $course->toUrl(),
      ];
    }

    return [
      '#theme' => 'item_list',
      '#title' => $this->t('Cursos destacados'),
      '#items' => $items,
      '#cache' => [
        'tags' => ['node_list'],
        'contexts' => ['user.permissions'],
      ],
    ];
  }
}
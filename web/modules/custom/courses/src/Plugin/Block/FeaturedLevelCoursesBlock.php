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
 * Displays courses filtered by level.
 */
#[Block(
    id: 'courses_by_level',
    admin_label: new TranslatableMarkup('Cursos por nivel'),
)]
class CoursesByLevelBlock extends BlockBase implements ContainerFactoryPluginInterface {

    public function __construct(array $configuration, $plugin_id, $plugin_definition, protected CourseManager $courseManager, ) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
    }

    public static function create( ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, ): static {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('course.manager'),
        );
    }

    public function defaultConfiguration(): array {
        return [
            'level' => 'i',
        ] + parent::defaultConfiguration();
    }

    public function blockForm( $form,FormStateInterface $form_state, ): array {
        $form = parent::blockForm($form, $form_state);

        $form['level'] = [
            '#type' => 'select',
            '#title' => $this->t('Nivel del curso'),
            '#options' => [
                'p' => $this->t('Principiante'),
                'i' => $this->t('Intermedio'),
                'a' => $this->t('Avanzado'),
            ],
            '#default_value' => $this->configuration['level'] ?? 'i',
            '#required' => TRUE,
        ];

        return $form;
    }

    public function blockSubmit(
        $form,
        FormStateInterface $form_state,
    ): void {
        parent::blockSubmit($form, $form_state);

        $this->configuration['level'] =
            $form_state->getValue('level');
    }

    public function build(): array {
        $level = $this->configuration['level'] ?? 'i';

        $courses = $this->courseManager->getCoursesByLevel($level);

        $items = [];

        foreach ($courses as $course) {
            $items[] = [
              'name' => $course->getTitle(),
              'url' => $course->toUrl()->toString(),
            ];
        }

        if (empty($items)) {
            return [
                '#type' => 'container',
                'message' => [
                    '#markup' => $this->t('No hay cursos para el nivel seleccionado.'),
                ],
            ];
        }

        return [
            '#theme' => 'course_featured',
            '#title' => $this->t('Cursos por nivel'),
            '#courses' => $items,
            '#cache' => [
                'tags' => ['node_list'],
                'contexts' => ['user.permissions'],
            ],
        ];
    }

}

<?php

namespace Drupal\courses\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\courses\Service\CourseManager;
use Override;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CourseSearchForm extends FormBase{

    protected CourseManager $courseManager;

    public function __construct(CourseManager $courseManager)
    {
        $this->courseManager = $courseManager;
    }

    public static function create(ContainerInterface $container):static {
        return new static($container->get('courses.manager'));
    }

    /**
     * Identificador unico del formulario
     */
    #[Override]
    public function getFormId(): string
    {
        return 'courses_search_form';
    }

    /**
     * Construye el formulario
     */
    #[Override]
    public function buildForm(array $form, FormStateInterface $form_state): array
    {
        $form['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nombre del curso'),
            '#description' => $this->t('Introduce un nombre o parte de él.'),
            '#default_value' => $form_state->getValue('name', '')
        ];

        $form['actions'] = [
            '#type' => 'actions'
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Buscar')
        ];

        // Si se ha enviado el formulario, mostramos los resultados
        if($form_state->get('search_submitted')){
            $query = (string) $form_state->getValue('name', '');
            $courses = $this->courseManager->findByName($query);

            $items = [];
            foreach ($courses as $course) {
                $items[] = $course['name'] . " - " . $course['price'] . "€";
            }

            $form['results'] = [
                '#theme' => 'item_list',
                '#title' => $this->t('Resultados'),
                '#items' => $items,
                '#empty' => $this->t('No se han encontrado cursos'),
            ];
        }

        return $form;
    }

    /**
     * Valida los datos recibidos
     */
    #[Override]
    public function validateForm(array &$form, FormStateInterface $form_state): void
    {
        $name = trim((string) $form_state->getValue('name'));

        if(strlen($name) > 10){
            $form_state->setErrorByName('name', $this->t('El nombre no puede superar los 10 caracteres.'));
        }
    }

    /**
     * Procesar el envio
     */

    #[Override]
    public function submitForm(array &$form, FormStateInterface $form_state): void
    {
        $form_state->set('search_submitted', TRUE);
        $form_state->setRebuild();
    }

}
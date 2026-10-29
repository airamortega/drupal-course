<?php

namespace Drupal\courses\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\courses\Service\CourseManager;
use Override;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CourseRegistrationForm extends FormBase
{

    protected CourseManager $courseManager;

    public function __construct(CourseManager $courseManager)
    {
        $this->courseManager = $courseManager;
    }

    public static function create(ContainerInterface $container): static
    {
        return new static($container->get('courses.manager'));
    }

    /**
     * Identificador unico del formulario
     */
    #[Override]
    public function getFormId(): string
    {
        return 'course_registration_form';
    }

    /**
     * Construye el formulario
     */
    #[Override]
    public function buildForm(array $form, FormStateInterface $form_state): array
    {

        $form['identity'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Datos principales'),
        ];

        $form['identity']['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nombre'),
            '#required' => true,
            '#maxlength' => 10,
            '#default_value' => $form_state->getValue('name', '')
        ];

        $form['identity']['description'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Descripción'),
            '#default_value' => $form_state->getValue('description', '')
        ];

        $form['price'] = [
            '#type' => 'number',
            '#title' => $this->t('Precio'),
            '#required' => true,
            '#default_value' => $form_state->getValue('Precio', '')
        ];

        $form['profesor'] = [
            '#type' => 'select',
            '#title' => $this->t('Profesor'),
            '#options' => [
                '' => $this->t('Selecciona una opción'),
                'david' => $this->t('David'),
                'pedro' => $this->t('Pedro'),
                'jose' => $this->t('Jose')
            ],
            '#default_value' => $form_state->getValue('profesor', '')
        ];

        $form['tags'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Etiquetas'),
            '#required' => true,
            '#options' => [
                'pr' => 'Programacion',
                'web' => ' Desarrollo Web',
                'back' => 'BackEnd',
                'front' => 'Frontend',
            ],
            '#default_value' => $form_state->getValue('tags', [])
        ];

        $results = $form_state->get('registration_result');

        $form['result'] = [
            '#type' => 'container',
            '#attributes' => [
                'id' => 'course-registration-result',
            ],
        ];

        if ($results !== null) {
            $form['result']['summary'] = [
                '#theme' => 'item_list',
                '#title' => $this->t('Datos procesados'),
                '#items' => [
                    $this->t('Nombre: @name', [
                        '@name' => $results['name'],
                    ]),
                    $this->t('Descripción: @description', [
                        '@description' => $results['description'],
                    ]),
                    $this->t('Precio: @price', [
                        '@price' => $results['price'],
                    ]),
                ],
            ];
        }

        $form['actions'] = [
            '#type' => 'actions'
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Registrar'),
            '#ajax' => [
                'callback' => '::ajaxRefreshResult',
                'wrapper' => 'course-registration-result'
            ],
        ];

        return $form;
    }

    /**
     * Valida los datos recibidos
     */
    #[Override]
    public function validateForm(array &$form, FormStateInterface $form_state): void
    {
        $name = trim((string) $form_state->getValue('name'));
        if ($name === '') {
            $form_state->setErrorByName('name', $this->t('El nombre no puede estar vacío.'));
        }

        $tags = (array) $form_state->getValue('tags') ?? [];
        $tags = array_filter($tags);

        if (count($tags) > 2) {
            $form_state->setErrorByName('tags',$this->t('Solo puedes seleccionar dos tecnologías.'));
        }
    }

    /**
     * Procesar el envio
     */

    #[Override]
    public function submitForm(array &$form, FormStateInterface $form_state): void
    {
        $data = [
            'name' => $form_state->getValue('name'),
            'description' => $form_state->getValue('description'),
            'price' => $form_state->getValue('price'),
            'profesor' => $form_state->getValue('profesor'),
            'tags' => array_values(array_filter($form_state->getValue('tags') ?? [])),
        ];

        $result = $this->courseManager->processRegistration($data);
        $form_state->set('registration_result', $result);
        $this->messenger()->addStatus($this->t('Los datos se han procesado correctamente.'));

        $form_state->setRebuild();
    }

    public function ajaxRefreshResult(array &$form, FormStateInterface $form_state)
    {
        return $form['result'];
    }
}

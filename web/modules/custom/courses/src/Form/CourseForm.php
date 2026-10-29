<?php

namespace Drupal\courses\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\courses\Service\CourseManager;
use Drupal\node\NodeInterface;
use Override;

class CourseForm extends FormBase {

    public function __construct(protected CourseManager $courseManager)
    {}

    public static function create(ContainerInterface $container): static{
        return new static($container->get('courses.manager'));
    }

    public function getFormId(): string
    {
        return 'courses_course_form';
    }

    #[Override]
    public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $curso = NULL)
    {
        $form['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nombre'),
            '#required' => true,
            '#maxlength' => 120,
            '#default_value' => $curso?->getTitle() ?? '',
        ];

        $form['desc'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Descripcion'),            
            '#default_value' => $curso?->get('field_desc')->value ?? '',
        ];

        $form['price'] = [
            '#type' => 'number',
            '#title' => $this->t('Precio'),            
            '#default_value' => $curso?->get('field_price')->value ?? 0,
        ];

        $form['level'] = [
            '#type' => 'select',
            '#title' => $this->t('Nivel'),
            '#options' => [                
                'p' => $this->t('Principiante'),
                'i' => $this->t('Intermedio'),
                'a' => $this->t('Avanzado')
            ],
            '#required' => true,
            '#default_value' => $curso?->get('field_level')->value ?? 'i',
        ];

        $form['available_course'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Plazas disponibles'),
            '#default_value' => (bool) $curso?->get('field_available_course')->value ?? true
        ];

        $form['actions'] = [
            '#type' => 'actions'
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $curso ? $this->t('Actualizar curso') : $this->t('Crear curso')
        ];

        if($curso !== null){
            $form_state->set('course_id', $curso->id());
        }

        return $form;
    }

    #[Override]
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $name = (string) $form_state->getValue('name');
        if(strlen($name) > 20){
            $form_state->setErrorByName('name', $this->t('El nombre no puede ser mayor a 20 caracteres.'));
        }
    }

    #[Override]
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $values = [
            'name' => $form_state->getValue('name'),
            'desc' => $form_state->getValue('desc'),
            'price' => $form_state->getValue('price'),
            'level' => $form_state->getValue('level'),
            'available' => $form_state->getValue('available'),
        ];

        $courseId = $form_state->get('course_id');

        if($courseId !== null){
            // Modificación
            $course = $this->courseManager->getCourse($courseId);

            if($course === null){
                throw new \Symfony\Component\Httpkernel\Exception\NotFoundHttpException();
            }

            $course = $this->courseManager->updateCourse($course, $values);

            $this->messenger()->addStatus($this->t('Se ha actualizado el curso @name.', ['@name' => $course->getTitle()]));

        }else{
            // Inserción
            $course = $this->courseManager->createCourse($values);
            $this->messenger()->addStatus($this->t('Se ha creado el curso @name.', ['@name' => $course->getTitle()]));
        }                

        $form_state->setRedirect('courses.list');
    }
}
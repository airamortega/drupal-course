<?php

namespace Drupal\developer_directory\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\developer_directory\Service\DeveloperDirectoryManager;
use Drupal\node\NodeInterface;
use Override;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeveloperForm extends FormBase{

    public function __construct(protected DeveloperDirectoryManager $directoryManager)
    { }

    #[Override]
    public static function create(ContainerInterface $container): static {
        return new static($container->get('developer_directory.manager'));
    }

    #[Override]
    public function getFormId(): string
    {
        return 'developer_directory_developer_form';
    }

    #[Override]
    public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $developer = NULL)
    {
        $form['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nombre'),
            '#required' => true,
            '#maxlength' => 120,
            '#default_value' => $developer?->getTitle() ?? '',
        ];

        $form['email'] = [
            '#type' => 'email',
            '#title' => $this->t('Email'),
            '#required' => true,
            '#default_value' => $developer?->get('field_email')->value ?? '',
        ];

        $form['bio'] = [
            '#type' => 'textsrea',
            '#title' => $this->t('Biografía'),
            '#default_value' => $developer?->get('field_bio')->value ?? '',
        ];

        $form['experience'] = [
            '#type' => 'select',
            '#title' => $this->t('Experiencia'),
            '#options'=>[
                'junior' => $this->t('Junior'),
                'mid' => $this->t('Intermedio'),
                'senior' => $this->t('Senior')
            ],
            '#required' => true,
            '#default_value' => $developer?->get('field_experience')->value ?? 'junior',
        ];

        $form['portfolio'] = [
            '#type' => 'url',
            '#title' => $this->t('Portfolio'),
            '#default_value' => $developer?->get('field_portfolio')->uri ?? ''
        ];

        $form['available'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Disponible para nuevos proyectos'),
            '#default_value' => (bool) $developer?->get('field_available')->value ?? false,
        ];

        $form['actions'] = [
            '#type' => 'actions'
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $developer ? $this->t('Actualizar') : $this->t('Crear')
        ];

        if($developer !== null){
            $form_state->set('developer_id', $developer->id());
        }

        return $form;

    }

    #[Override]
    public function validateForm(array &$form, FormStateInterface $form_state): void
    {

        $email = trim((string) $form_state->getValue('email'));
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $form_state->setErrorByName('email', $this->t('Debe introducir un email válido.'));
        }
    }

    #[Override]
    public function submitForm(array &$form, FormStateInterface $form_state): void
    {   

        $values = [
            'name' => $form_state->getValue('name'),
            'email' => $form_state->getValue('email'),
            'portfolio' => $form_state->getValue('portfolio'),
            'experience' => $form_state->getValue('experience'),
            'bio' => $form_state->getValue('bio'),
            'available' => $form_state->getValue('available'),
        ];

        $developerId = $form_state->get('developer_id');

        if($developerId !== null){
            $developer = $this->directoryManager->getDeveloper($developerId);

            if($developer === null){
                throw new \Symfony\component\httpkernel\exception\NotFoundHttpException();
            }

            $developer = $this->directoryManager->updateDeveloper($developer, $values);
            $this->messenger()->addStatus($this->t('Se ha actualizado el desarrollador @name.', ['@name' => $developer->getTitle()]));

        }else{
            $developer = $this->directoryManager->createDeveloper($values);
            $this->messenger()->addStatus($this->t('Se ha creado el desarrollador @name.', ['@name' => $developer->getTitle()]));
        }
        
        $form_state->setRedirect('developer_directory.list');

    }

}
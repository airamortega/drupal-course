<?php

declare(strict_types=1);

namespace Drupal\courses\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\courses\Service\CourseManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Returns responses for Courses routes.
 */
final class CoursesController extends ControllerBase {

    protected CourseManager $courseManager;

    /**
     * Constructor.
     */
    public function __construct(CourseManager $courseManager) {
        $this->courseManager = $courseManager;
    }

    /**
     * Crea el controlador utilizando el contenedor de Drupal.
     */
    public static function create(ContainerInterface $container): static {
        return new static($container->get('courses.manager'));
    }

    /**
     * Listado de cursos.
     */
    public function list(): array {

        $courses = $this->courseManager->getCourses();

        $items = [];
        foreach($courses as $course){
            $items[] = [
                '#type' => 'link',
                '#title' => $course->getTitle() . " - " . $course->get('field_price')->value . " euros ",
                '#url' => \Drupal\Core\Url::fromRoute('courses.view', ['id' => $course->id()])
            ];
            
            $items[] = [
                '#type' => 'link',
                '#title' => 'Editar',
                '#url' => \Drupal\Core\Url::fromRoute('courses.edit', ['curso' => $course->id()])
            ];
        }

        $build['content'] = [
                '#theme' => 'item_list',
                '#title' => $this->t('Cursos'),
                '#items' => $items
            ];

        return $build;

    }
    

    #[Route(
        '/courses/{id}',
        name: 'courses.view',
        requirements: [
        '_permission' => 'access content',
        'id' => '\d+',
        ]
    )]

    public function view(int $id): array {
    
        $course = $this->courseManager->getCourse($id);

        if(!$course) throw new NotFoundHttpException(); 
    
        $levelField = $course->get('field_level');
        $levelOptions = $levelField->getFieldDefinition()->getSetting('allowed_values');
        $levelLabel = $levelOptions[$levelField->value] ?? $levelField->value;

        return [
            '#theme' => 'course_detail',
            '#name' => $course->getTitle(),      
            '#desc' => $course->get('field_desc')->value,
            '#price' => $course->get('field_price')->value,
            '#available_course' => (bool) $course->get('field_available_course')->value,
            '#level' => $levelLabel
        ];

    }
    
    public function admin(): array {
        // Aquí puedes agregar la lógica para la página de administración de cursos.
        return [
        '#markup' => '<h1>Administración de Cursos</h1><p>Esta es la página de administración de cursos.</p>',
        ];
    }
}
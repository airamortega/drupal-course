<?php

namespace Drupal\courses\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Gestiona las operaciones de los cursos
 * 
 */

class CourseManager{

    public function __construct(protected EntityTypeManagerInterface $entityManager)
    { }

    public function getCourses(int $limit = 10): array{
        $storage = $this->entityManager->getStorage('node');

        $ids = $storage->getQuery()
            ->accessCheck(true)
            ->condition('type', 'curso')
            ->condition('status', 1)
            ->sort('title', 'ASC')
            ->range(0, max(1, $limit))
            ->execute();
        
        return $storage->loadMultiple($ids);
    }

    public function getCourse(int $id): ?NodeInterface{
        $node = $this->entityManager->getStorage('node')->load($id);

        if(!$node instanceof NodeInterface){
            return null;
        }

        if($node->bundle() !== 'curso' || !$node->access('view')){
            return null;
        }

        return $node;
    }

    public function findByName(string $query): array {
        $matches = [];
        $query = trim($query);

        foreach($this->getCourses() as $course){
            if($query === '' || stripos($course['name'], $query) !== FALSE){
                $matches[] = $course;
            }
        }

        return $matches;
    }

    public function getCoursesByLevel(string $level): array {
        $storage = $this->entityManager->getStorage('node');

        $ids = $storage->getQuery()
            ->accessCheck(TRUE)
            ->condition('type', 'curso')
            ->condition('field_level', $level)
            ->sort('title', 'ASC')
            ->execute();

        return $storage->loadMultiple($ids);
    }

    public function processRegistration(array $data): array{

        return[
            'name'=> trim($data['name']),
            'description'=> trim($data['description']),
            'price'=> trim($data['price'] ?? ''),
            'rating'=> $data['rating'],
            'tags'=>$data['tags'] ?? [],
            'teacher'=> $data['teacher'] ,
        ];

    }

    public function createCourse(array $values): NodeInterface{
        $storage = $this->entityManager->getStorage('node');

        $course = $storage->create([
            'type' => 'curso',
            'title' => $values['name'],
            'field_desc' => $values['desc'],
            'field_price' => $values['price'],
            'field_level' => $values['level'],
            'field_available_course' => $values['available_course'],
            'status' => 1,
        ]);

        $course->save();

        return $course;
    }

    public function updateCourse(NodeInterface $curso, array $values): NodeInterface{

        $curso->setTitle($values['name']);
        $curso->set('field_desc', $values['desc']);
        $curso->set('field_price', $values['price']);
        $curso->set('field_level', $values['level']);
        $curso->set('field_available_course', $values['available_course'] ?? 0);

        $curso->save();

        return $curso;
    }
}
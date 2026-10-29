<?php

namespace Drupal\developer_directory\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Gestiona las operacion del directorio de desarrolladores
 * 
 */

class DeveloperDirectoryManager{

    public function __construct(protected EntityTypeManagerInterface $entityManager){}

    public function getDevelopers(): array{

        $storage = $this->entityManager->getStorage('node');

        $ids = $storage->getQuery()
            ->accessCheck(TRUE)
            ->condition('type', 'developer')
            ->condition('status', 1)
            ->sort('title', 'ASC')
            ->execute();

        return $storage->loadMultiple($ids);

    }

    public function findByName(string $query): array {
        $matches = [];
        $query = trim($query);

        foreach($this->getDevelopers() as $developer){
            if($query === '' || stripos($developer['name'], $query) !== FALSE){
                $matches[] = $developer;
            }
        }

        return $matches;
    }

    public function getDeveloper(string $id): ?NodeInterface {
        $node = $this->entityManager->getStorage('node')->load($id);

        if(!$node instanceof NodeInterface){
            return null;
        }

        if($node->bundle() !== 'developer' || !$node->access('view')){
            return null;
        }

        return $node;
    }

    public function processRegistration(array $data): array{

        return[
            'name'=> trim($data['name']),
            'email'=> trim($data['email']),
            'portfolio'=> trim($data['portfolio'] ?? ''),
            'experience'=> $data['experience'],
            'technologies'=>$data['technologies'] ?? [],
            'available'=> !empty($data['available']) ,
            'comments'=> trim($data['comments'] ?? '')
        ];

    }

    public function createDeveloper(array $values): NodeInterface{

         $storage = $this->entityManager->getStorage('node');

         $developer = $storage->create([
            'type' => 'developer',
            'title' => $values['name'],
            'field_email' => $values['email'],
            'field_bio' => $values['bio'],
            'field_experience' => $values['experience'],
            'field_portfolio' => $values['portfolio'],
            'field_available' => $values['available'],
            'status' => 1,
         ]);

         $developer->save();

         return $developer;

    }

    public function updateDeveloper(NodeInterface $developer, array $values): NodeInterface{

        $developer->setTitle($values['name']);
        $developer->set('field_email', $values['email']);
        $developer->set('field_bio', $values['bio']);
        $developer->set('field_experience', $values['experience']);
        $developer->set('field_portfolio', ['uri' => $values['portfolio']]);
        $developer->set('field_available', $values['available']);

        $developer->save();

        return $developer;

    }
}
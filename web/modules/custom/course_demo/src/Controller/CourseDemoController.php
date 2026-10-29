<?php

namespace Drupal\course_demo\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class CourseDemoController.
 *
 * @package Drupal\course_demo\Controller
 * 
 */

class CourseDemoController extends ControllerBase {
    
  /**
   * Hello.
   *
   * @return array
   *   Return Hello array.
   */
  public function hello(string $name): array {
    return [
      '#markup' => $this->t('Hello @name!', ['@name' => $name]),
    ];
  }

  /**
   * admin.
   *
   * @return array
   *   Controlador de la página de administración del módulo course demo.
   */
  public function admin(): array {
    return [
      '#markup' => $this->t('Página de administración de course demo.'),
    ];
  }

}

?>
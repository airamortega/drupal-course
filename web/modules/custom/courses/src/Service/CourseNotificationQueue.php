<?php

namespace Drupal\courses\Service;

use Drupal\Core\Queue\QueueFactory;

final class CourseNotificationQueue {
    
  public function __construct(private QueueFactory $queueFactory) 
  { }

  public function enqueue(int $courseId, string $operation): void {
    $queue = $this->queueFactory->get('course_notification');
    $queue->createItem([
      'course_id' => $courseId,
      'operation' => $operation,
    ]);

  }
}
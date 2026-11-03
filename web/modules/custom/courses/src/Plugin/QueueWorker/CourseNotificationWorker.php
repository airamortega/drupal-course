<?php

namespace Drupal\courses\Plugin\QueueWorker;


use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\Attribute\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[QueueWorker(
  id: "course_notification",
  title: new TranslatableMarkup("Course Notification Queue Worker"),
    cron: ['time' => 30],
)]
final class CourseNotificationWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

    public function __construct(array $configuration, $plugin_id, $plugin_definition, protected LoggerChannelInterface $logger) {
        return parent::__construct($configuration, $plugin_id, $plugin_definition);
    }

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('logger.factory')->get('courses'),
        );
    }

    public function processItem($data): void {

        if(!is_array($data) || empty($data['course_id'])) {
            $this->logger->warning('Se ha recibido una notificacion sin ID de curso.');
            return;
        }

        $courseId = $data['course_id'];
        $operation = $data['operation'] ?? 'unknown';

        $this->logger->info("Procesada la notificacion del curso @id, con operacion @operation.", [
            '@id' => $courseId,
            '@operation' => $operation,
        ]);
    }
}
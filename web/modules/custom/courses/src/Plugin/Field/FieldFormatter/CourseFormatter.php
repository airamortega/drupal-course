<?php

declare(strict_types=1);

namespace Drupal\courses\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[FieldFormatter(
  id: 'courses_estilos',
  label: new TranslatableMarkup('Estilo para los cursos'),
  field_types: ['list_string'],
)]
final class CourseFormatter extends FormatterBase {

  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];

    $elements['#attached']['library'][] = 'courses/estilos';

    $allowed_values = $this->fieldDefinition
      ->getFieldStorageDefinition()
      ->getSetting('allowed_values');

    foreach ($items as $delta => $item) {
      $value = $item->value;
      $label = $allowed_values[$value] ?? $value;

      $classes = match ($value) {
        'p' => ['level-badge', 'level-badge--junior'],
        'i' => ['level-badge', 'level-badge--mid'],
        'm' => ['level-badge', 'level-badge--senior'],
        default => ['level-badge'],
      };

      $elements[$delta] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => $classes,
        ],
        'label' => [
          '#plain_text' => (string) $label,
        ],
      ];
    }

    return $elements;
  }  
}
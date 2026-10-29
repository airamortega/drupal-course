<?php

declare(strict_types=1);

namespace Drupal\courses\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Attribute\ViewsFilter;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

#[ViewsFilter('course_title_prefix')]
final class CourseTitlePrefix extends FilterPluginBase {
    
  protected function defineOptions(): array {
    $options = parent::defineOptions();
    $options['value'] = ['default' => '',];
    return $options;
  }

    protected function valueForm(&$form, FormStateInterface $form_state,): void {
        $letters = ['' => $this->t('- Cualquier inicial -'),];

        foreach (range('A', 'Z') as $letter) {
            $letters[$letter] = $letter;
        }

        $form['value'] = [
        '#type' => 'select',
        '#title' => $this->t('Letra inicial'),
        '#options' => $letters,
        '#default_value' => $this->value ?? '',
        ];
    }

  public function query(): void {
    $value = $this->value;

    if (is_array($value)) $value = reset($value);

    if (!is_string($value) || $value === '') {
      return;
    }

    $this->ensureMyTable();

    $this->query->addWhereExpression(
      $this->options['group'],
      'UPPER(' . $this->tableAlias . '.title) LIKE :course_title_prefix',
      [
        ':course_title_prefix' => strtoupper($value) . '%',
      ],
    );
  }

  public function adminSummary(): string {
    if (empty($this->value)) {
      return (string) $this->t('Cualquier inicial');
    }

    return (string) $this->t(
      'Título que comienza por @letter',
      ['@letter' => $this->value],
    );
  }
}
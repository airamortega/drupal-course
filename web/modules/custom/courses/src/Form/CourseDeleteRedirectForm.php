<?php

namespace Drupal\courses\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Url;
use Override;

class CourseDeleteRedirectForm extends ContentEntityDeleteForm {
    #[Override]
    protected function getRedirectUrl()
    {
        return Url::fromRoute('courses.list');
    }
}
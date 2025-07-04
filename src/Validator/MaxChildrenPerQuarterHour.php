<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MaxChildrenPerQuarterHour extends Constraint
{
    public $message = 'Le nombre maximum d\'enfants (20) est déjà atteint pour le créneau de {{ time }}.';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
} 
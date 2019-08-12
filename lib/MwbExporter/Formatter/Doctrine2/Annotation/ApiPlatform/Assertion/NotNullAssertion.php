<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Assertion;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Column;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;
use MwbExporter\Object\Annotation;

/**
 * Applies NotNull assertion where needed respecting diagram specification
 *
 * Class NotNullAssertion
 * @package MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Assertion
 */
class NotNullAssertion implements PropertyLevelAssertionAnnotationInterface
{
    public function buildAnnotation(Column $column): ?string
    {
        if($column->getNullableValue() == null) {
            return ' * @NotNull()';
        }

        return null;
    }

    public function buildAnnotationForJoinColumn(array $joinAnnotation): ?string
    {
        if(isset($joinAnnotation['nullable'])) {
            if($joinAnnotation['nullable'] == false) {
                return ' * @NotNull()';
            }
        }
        return null;
    }

    public function getUsage(): string
    {
        return 'Symfony\Component\Validator\Constraints\NotNull';
    }
}

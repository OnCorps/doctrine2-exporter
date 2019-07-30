<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps\Assertion;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Column;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;
use MwbExporter\Object\Annotation;

/**
 * Builds Unique Entity assertion analysing the primaries in the table.
 *
 * Class UniqueEntityAssertion
 * @package MwbExporter\Formatter\Doctrine2\Annotation\OnCorps\Assertion
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

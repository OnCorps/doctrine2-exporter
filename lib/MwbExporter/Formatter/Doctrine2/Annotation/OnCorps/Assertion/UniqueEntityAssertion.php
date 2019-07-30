<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps\Assertion;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;

/**
 * Builds Unique Entity assertion analysing the primaries in the table.
 *
 * Class UniqueEntityAssertion
 * @package MwbExporter\Formatter\Doctrine2\Annotation\OnCorps\Assertion
 */
class UniqueEntityAssertion implements ClassLevelAssertionAnnotationInterface
{
    public function buildAnnotation(Table $table): string
    {
        $primaries = [];
        foreach ($table->getColumns() as $column) {
            if ($column->isPrimary()) {
                $primaries[] = '"' . $column->getColumnName() . '"';
            }
        }

        if (count($primaries) == 0) {
            return null;
        }

        $primaries = implode(",", $primaries);

        return ' * @UniqueEntity(fields={' . $primaries . '},message="' . str_replace('"', '', $primaries) . ' needs to be unique")';
    }

    public function getUsage(): string
    {
        return 'Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity';
    }
}

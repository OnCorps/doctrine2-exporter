<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Assertion;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Column;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;

/**
 * Builds Unique Entity assertion analysing the primaries in the table.
 *
 * Class UniqueEntityAssertion
 * @package MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Assertion
 */
class UniqueEntityAssertion implements ClassLevelAssertionAnnotationInterface
{
    public function buildAnnotation(Table $table): ?string
    {
        $primaries = [];
        /** @var Column $column */
        foreach ($table->getColumns() as $column) {
            if ($column->isPrimary()) {
                $primaries[] = '"' . $column->getPropertyName() . '"';
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

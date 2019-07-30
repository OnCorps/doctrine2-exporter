<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps\Assertion;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Column;

interface PropertyLevelAssertionAnnotationInterface extends AssertionAnnotationInterface
{
    public function buildAnnotation(Column $table): ?string;
}

<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps\Assertion;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;

interface ClassLevelAssertionAnnotationInterface extends AssertionAnnotationInterface
{
    public function buildAnnotation(Table $table): ?string;
}

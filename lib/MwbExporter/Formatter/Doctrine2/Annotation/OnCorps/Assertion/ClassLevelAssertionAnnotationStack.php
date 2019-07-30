<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps\Assertion;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Column;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;

abstract class ClassLevelAssertionAnnotationStack
{

    /** @var array */
    private $assertionAnnotationStack;

    /**
     * Validates the presence of AssertionAnnotationInterface classes in stack
     *
     * AssertionAnnotation constructor.
     *
     * @param array $assertionAnnotation
     */
    public function __construct(array $assertionBuilderClasses)
    {
        foreach ($assertionBuilderClasses as $assertionBuilderClass) {
            if (
                !($assertionBuilderClass instanceof ClassLevelAssertionAnnotationInterface)
                &&
                !($assertionBuilderClass instanceof AssertionAnnotationInterface)
            ) {
                throw new \RuntimeException(
                    'Only ClassLevelAssertionAnnotationInterface are allowed as constructor array for ClassLevelAssertionAnnotationStack'
                );
            }
        }

        $this->assertionAnnotationStack = $assertionBuilderClasses;
    }

    public function getStack(): array
    {
        return $this->assertionAnnotationStack;
    }
}

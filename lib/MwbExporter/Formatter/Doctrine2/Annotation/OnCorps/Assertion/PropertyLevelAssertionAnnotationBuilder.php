<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps\Assertion;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Column;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;

class PropertyLevelAssertionAnnotationBuilder extends PropertyLevelAssertionAnnotationStack
{
    /**
     * ClassLevelAssertionAnnotationBuilder constructor.
     *
     * @param array $assertionBuilderClasses
     */
    public function __construct(array $assertionBuilderClasses)
    {
        parent::__construct($assertionBuilderClasses);
    }

    /**
     * @param Column $column
     *
     * @return array|null array of strings (annotation to be written), null if none found.
     */
    public function buildAnnotations(Column $column): ?array
    {
        $classLevelAnnotations = [];

        /** @var PropertyLevelAssertionAnnotationInterface $assertionAnnotationClass */
        foreach ($this->getStack() as $assertionAnnotationClass) {
            try {
                $propertyLevelAnnotations[] = $assertionAnnotationClass->buildAnnotation($column);
                //don't let one break all the others:
            } catch (\Exception $exception) {
                //@todo a logger would be better here:
                echo $exception->getMessage();
            }
        }

        if (count($propertyLevelAnnotations) == 0) {
            return null;
        }

        return $propertyLevelAnnotations;
    }
}

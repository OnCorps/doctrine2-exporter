<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Assertion;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Column;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;

class ClassLevelAssertionAnnotationBuilder extends ClassLevelAssertionAnnotationStack
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
     * @param Table $table
     *
     * @return array|null array of strings (annotation to be written), null if none found.
     */
    public function buildAnnotations(Table $table): ?array
    {
        $classLevelAnnotations = [];

        /** @var ClassLevelAssertionAnnotationInterface $assertionAnnotationClass */
        foreach ($this->getStack() as $assertionAnnotationClass) {
            try {
                $classLevelAnnotations[] = $assertionAnnotationClass->buildAnnotation($table);
                //don't let one break all the others:
            } catch (\Exception $exception) {
                //@todo a logger would be better here:
                echo $exception->getMessage();
            }
        }

        if (count($classLevelAnnotations) == 0) {
            return null;
        }

        return $classLevelAnnotations;
    }
}

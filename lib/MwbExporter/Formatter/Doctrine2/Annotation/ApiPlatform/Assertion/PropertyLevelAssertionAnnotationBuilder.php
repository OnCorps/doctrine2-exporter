<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Assertion;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Column;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;
use MwbExporter\Object\Annotation;

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
        $propertyLevelAnnotations = [];

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

    /**
     * @param Annotation $joinAnnotation
     *
     * @return array|null
     * @throws \ReflectionException
     */
    public function buildAnnotationsForJoinColumn(Annotation $joinAnnotation) {
        $propertyLevelAnnotations = [];

        //force access the ORM annotation to then pass his content to the stack
        //which would decide if write the annotations or not
        //try catch all as we'd rather loose an annotation than everything else
        try{
            $reflection = new \ReflectionClass($joinAnnotation);
            $property = $reflection->getProperty('content');
            $property->setAccessible(true);
            $annotationContent = $property->getValue($joinAnnotation);

            /** @var PropertyLevelAssertionAnnotationInterface $assertionAnnotationClass */
            foreach ($this->getStack() as $assertionAnnotationClass) {
                try {
                    $propertyLevelAnnotations[] = $assertionAnnotationClass->buildAnnotationForJoinColumn($annotationContent);
                    //don't let one break all the others:
                } catch (\Exception $exception) {
                    //@todo a logger would be better here:
                    echo $exception->getMessage();
                }
            }

            if (count($propertyLevelAnnotations) == 0) {
                return null;
            }
        }catch (\Exception $exception){
            return null;
        }

        return $propertyLevelAnnotations;
    }
}

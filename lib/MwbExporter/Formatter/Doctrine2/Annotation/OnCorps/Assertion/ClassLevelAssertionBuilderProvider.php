<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps\Assertion;

/**
 * Config class for tuning the stack for the builder and getting it
 * Class ClassLevelBuilderProvider
 * @package MwbExporter\Formatter\Doctrine2\Annotation\OnCorps\Assertion
 */
class ClassLevelAssertionBuilderProvider
{
    /**
     * @return ClassLevelAssertionAnnotationBuilder
     */
    public function getClassLevelAssertionBuilder()
    {
        $builder = new ClassLevelAssertionAnnotationBuilder(
            [
                new UniqueEntityAssertion(),
                //Add more class level assertion builder single class here, the stack will call the rest
            ]
        );

        return $builder;
    }

    /**
     * Foreach configured single classLevelAssertionBuilder in getClassLevelAssertionBuilder returns his usage
     * @return array
     */
    public function getUsages(): array {
        $uses = [];
        /** @var ClassLevelAssertionAnnotationInterface $classLevelAssertionBuilder */
        foreach ($this->getClassLevelAssertionBuilder()->getStack() as $classLevelAssertionBuilder) {
            $uses[] = $classLevelAssertionBuilder->getUsage();
        }

        return $uses;
    }
}

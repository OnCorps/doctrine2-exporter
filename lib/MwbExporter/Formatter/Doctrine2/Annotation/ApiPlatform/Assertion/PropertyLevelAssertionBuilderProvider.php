<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Assertion;

/**
 * Config class for tuning the stack for the builder and getting it
 * Class ClassLevelBuilderProvider
 * @package MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Assertion
 */
class PropertyLevelAssertionBuilderProvider
{
    /**
     * @return PropertyLevelAssertionAnnotationBuilder
     */
    public function getPropertyLevelAssertionBuilder()
    {
        $builder = new PropertyLevelAssertionAnnotationBuilder(
            [
                new NotNullAssertion(),
                //Add more property level assertion builder single class here, the stack will call the rest
            ]
        );

        return $builder;
    }

    /**
     * Foreach configured single propertyLevelAssertionBuilder in getPropertyLevelAssertionBuilder returns his usage
     * @return array
     */
    public function getUsages(): array {
        $uses = [];
        /** @var PropertyLevelAssertionAnnotationStack $propertyLevelAssertionBuilder */
        foreach ($this->getPropertyLevelAssertionBuilder()->getStack() as $propertyLevelAssertionBuilder) {
            $uses[] = $propertyLevelAssertionBuilder->getUsage();
        }

        return $uses;
    }
}

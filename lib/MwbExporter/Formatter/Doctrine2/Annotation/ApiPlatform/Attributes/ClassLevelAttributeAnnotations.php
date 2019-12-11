<?php

namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Attributes;

use MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Attributes\PaginationAttribute;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;

/**
 * Class ApiPlatformResourceAnnotations
 * @package MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform
 */
class ClassLevelAttributeAnnotations
{

    const CLASS_LEVEL_ATTRIBUTE_BUILDER_CLASSES = [
        PaginationAttributeBuilder::class,
        // Please extend this list when create a new Attribute Builder that needs to be used
    ];

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var ClassLevelAttributeInterface[]
     */
    private $classLevelAttributes;

    /**
     * @var array
     */
    private $annotations = [];

    /**
     * @var Table
     */
    private $table;

    /**
     * ApiPlatformClassLevelAttributeAnnotations constructor.
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $classLevelAttributeBuilderClasses = self::CLASS_LEVEL_ATTRIBUTE_BUILDER_CLASSES;
        $commentSettingGenerator = new CommentSettingGenerator();
        foreach ($classLevelAttributeBuilderClasses as $classLevelAttributeBuilderClass) {
            $classLevelAttributeBuilder = new $classLevelAttributeBuilderClass($table, $commentSettingGenerator);
            $classLevelAttributes[] = $classLevelAttributeBuilder;
            if (!in_array(ClassLevelAttributeInterface::class, class_implements($classLevelAttributeBuilder))) {
                throw new \Exception(sprintf(
                    '%s needs to implement %s',
                    $classLevelAttributes,
                    ClassLevelAttributeInterface::class
                ));
            }
        }

        $this->table = $table;
        $this->classLevelAttributes = $classLevelAttributes;
    }

    /**
     * @return array
     */
    public function getAnnotations(): array
    {
        return $this->buildAnnotations();
    }

    private function buildAnnotations(): array
    {
        $table = $this->table;

        // Verify if we do have at least one class attribute annotation:
        foreach ($this->classLevelAttributes as $classLevelAttribute) {
            if($classLevelAttribute->buildAttribute()) {
                $this->attributes[] = $classLevelAttribute->buildAttribute();
            }
        }

        $this->annotations[] = $table->getAnnotation('@ApiResource(', null, [], '');

        if (empty($this->attributes)) {
            $this->annotations[0] .=')';
            return $this->annotations;
        }


        $this->annotations[] = '    attributes={';
        foreach ($this->attributes as $attribute) {
            $this->annotations[] = '    ' . $attribute . ',';
        }

        $this->annotations[] = '    }';
        $this->annotations[] = ')';

        return $this->annotations;
    }
}

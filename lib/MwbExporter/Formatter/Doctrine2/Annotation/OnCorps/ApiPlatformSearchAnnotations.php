<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2019-07-24
 * Time: 14:18
 */

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps;

use MwbExporter\Formatter\Doctrine2\Model;

/**
 * Class ApiPlatformSearchAnnotations
 * @package MwbExporter\Formatter\Doctrine2\Annotation\OnCorps
 */
class ApiPlatformSearchAnnotations extends ApiPlatformFieldAnnotations
{

    /**
     * @var array
     */
    protected $typeInfo = [
        'integer' => [
            'class' => 'RangeFilter::class',
            'delimiter' => ':',
            'modifiers' => [],
        ],
        'boolean' => [
            'class' => 'BooleanFilter::class',
            'delimiter' => ':',
            'modifiers' => [],
        ],
        'string' => [
            'class' => 'SearchFilter::class',
            'delimiter' => ':',
            'modifiers' => ['partial'],
        ],
        '\DateTime' => [
            'class' => 'DateFilter::class',
            'delimiter' => ':',
            'modifiers' => [],
        ]
    ];

    /**
     * @param Model\Table $table
     * @param string      $fieldName
     * @param string      $type
     *
     * @return string
     */
    public function buildAnnotationProperty(Model\Table $table, Model\Column $column, string $type): string
    {
        $name = $column->getColumnName();
        $converter = $table->getFormatter()->getDatatypeConverter();
        $nativeType = $converter->getNativeType($converter->getMappedType($column));
        if ($nativeType == $type) {
            return $this->generateAnnotationPropertyDetails($name, $nativeType);
        }
        return "";
    }
}

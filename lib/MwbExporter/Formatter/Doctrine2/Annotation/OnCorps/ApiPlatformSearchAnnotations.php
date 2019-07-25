<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2019-07-24
 * Time: 14:18
 */

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps;

use MwbExporter\Formatter\Doctrine2\Model;

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
     *
     * @return $this
     */
    public function buildAnnotations(Model\Table $table)
    {
        $converter = $table->getFormatter()->getDatatypeConverter();

        /** @var Column $column */
        foreach($table->getColumns() as $column) {
            $name = $column->getColumnName();
            if(isset($this->fields[$name])) {
                $nativeType = $converter->getNativeType($converter->getMappedType($column));
                if(isset($this->typeInfo[$nativeType])) {
                    $this->annotations[] = $this->generateAnnotation($name, $nativeType);
                }
            }
        }
        return $this;
    }
}

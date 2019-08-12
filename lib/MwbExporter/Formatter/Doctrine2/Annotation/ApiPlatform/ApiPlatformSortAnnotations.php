<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2019-07-24
 * Time: 14:18
 */

namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform;

use MwbExporter\Formatter\Doctrine2\Model;

class ApiPlatformSortAnnotations extends ApiPlatformFieldAnnotations
{
    /**
     * @var array
     */
    protected $typeInfo = [
        'default' => [
            'class' => 'OrderFilter::class',
            'delimiter' => ':',
            'modifiers' => ['asc'],
        ],
    ];

    /**
     * @param string      $fieldName
     * @param string      $type
     *
     * @return string
     */
    public function buildAnnotationProperty(Model\Column $column, string $type): string
    {
        $name = $column->getColumnName();
        return $this->generateAnnotationPropertyDetails($name, 'default');
    }

}

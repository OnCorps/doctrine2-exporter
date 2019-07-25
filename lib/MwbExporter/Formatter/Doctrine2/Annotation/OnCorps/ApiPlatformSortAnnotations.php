<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2019-07-24
 * Time: 14:18
 */

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps;

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
     * @param Model\Table $table
     *
     * @return $this
     */
    public function buildAnnotations(Model\Table $table)
    {
        /** @var Column $column */
        foreach($table->getColumns() as $column) {
            $name = $column->getColumnName();
            if(isset($this->fields[$name])) {
                $this->annotations[] = $this->generateAnnotation($name, 'default');
            }
        }
        return $this;
    }

}

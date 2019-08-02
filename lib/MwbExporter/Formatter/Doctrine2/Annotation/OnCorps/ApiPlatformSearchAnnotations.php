<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2019-07-24
 * Time: 14:18
 */

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps;

use MwbExporter\Formatter\Doctrine2\Model;
use MwbExporter\Model\ForeignKey;

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
        $converter = $table->getFormatter()->getDatatypeConverter();
        $nativeType = $converter->getNativeType($converter->getMappedType($column));
        if ($nativeType != $type) {
            return '';
        }
        $name = $column->getColumnName();
        $foreginKeys = $column->getForeignKeys();
        $filterName = null;
        /** @var ForeignKey $foreign */
        foreach($foreginKeys as $foreign) {
            $targetEntity = $foreign->getReferencedTable()->getModelName();
            $related = $table->getRelatedName($foreign);
            $filterName = lcfirst($table->getRelatedVarName($targetEntity, $related));
            if(!(
                in_array('partial', $this->fields[$name]->modifiers)
                    ||
                in_array('exact', $this->fields[$name]->modifiers)
                )
            ) {
                //Unless there is an explicit override for then the default for an FK is exact
                $this->fields[$name]->modifiers[] = 'exact';
            };
            $this->fields[$name]->filterName = $filterName;
            if(count($foreginKeys) > 1) {
                //Putting a break here because at this point I don't know when/how there would be more than one fk entry
                //but I don't want to just assume the last entry is the correct one or if some compound approach may be required.
                //Going to assume first and use that and then write to output and warn user
                print_r("Multiple foreign keys found on column {$name} for {$targetEntity} on table {$table->getName()}\n");
                break;
            }
        }

        return $this->generateAnnotationPropertyDetails($name, $nativeType);


    }
}

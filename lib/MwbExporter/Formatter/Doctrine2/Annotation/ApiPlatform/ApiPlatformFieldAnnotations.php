<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2019-07-24
 * Time: 16:21
 */

namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform;

use Doctrine\Common\Inflector\Inflector;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Column;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;
use MwbExporter\Formatter\Doctrine2\Model;

/**
 * Class ApiPlatformFieldAnnotations
 * @package MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform
 */
abstract class ApiPlatformFieldAnnotations
{
    /**
     * @param Column      $column
     * @param string      $fieldName
     *
     * @return string
     */
    abstract function buildAnnotationProperty(Column $column, string $type): string;

    protected function getKeyFields()
    {

    }

    const COMMENT_FIELD_DELIMITER = ",";
    const COMMENT_FIELD_PROPERTIES_DELIMITER = ":";
    const PROPERTIES_ANNOTATION_DELIMITER = ',';

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $typeInfo = [];

    /**
     * @var array
     */
    protected $annotations = [];


    /**
     * @var Table
     */
    protected $table;

    /**
     * ApiPlatformFieldAnnotations constructor.
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * @return bool
     */
    public function hasFields(): bool
    {
        return (bool)count($this->fields);
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getTypeInfo(): array
    {
        return $this->typeInfo;
    }

    /**
     * @param string $comment
     *
     * @return ApiPlatformFiltersBase
     */
    public function processFields(?string $comment = null): self
    {
        $this->getKeyFields();

        if(empty($comment)) {
            return $this;
        }

        $rawApiFilters = explode(SELF::COMMENT_FIELD_DELIMITER, strtolower(trim($comment)));
        if (count($rawApiFilters) === 0 || $rawApiFilters[0] == "") {
            return $this;
        }

        foreach($rawApiFilters as $rawFilter) {
            $rawFilterDetails = explode(self::COMMENT_FIELD_PROPERTIES_DELIMITER, trim($rawFilter));
            if (count($rawFilterDetails) > 0 && $rawFilterDetails[0] != "") {
                $name = array_shift($rawFilterDetails);
                $name = Inflector::camelize($name);
                $this->fields[$name] =  (object)[
                    'filterName' => $name,
                    'name' => $name,
                    'modifiers' => $rawFilterDetails,
                ];
            }
        }
        return $this;
    }

    /**
     * @param string $fieldName
     * @param string $typeKey
     *
     * @return string
     */
    public function generateAnnotationPropertyDetails(string $fieldName, ?string $typeKey = ""): string
    {
        $typeInfo = (object)$this->typeInfo[$typeKey];
        $details = '"'.$this->fields[$fieldName]->filterName.'"';
        if(count($this->fields[$fieldName]->modifiers)) {
            $details .= $typeInfo->delimiter.'"'.implode('"'.$typeInfo->delimiter.'"',$this->fields[$fieldName]->modifiers).'"';
        } elseif(count($typeInfo->modifiers)) {
            $details .= $typeInfo->delimiter.'"'.implode('"'.$typeInfo->delimiter.'"',$typeInfo->modifiers).'"';
        }

        return $details;
    }

    /**
     * @param Model\Table $table
     *
     * @return $this
     */
    public function buildAnnotations()
    {
        $table = $this->table;

        $this->annotations = [];

        foreach ($this->typeInfo as $type => $typeInfoArray) {
            $typeInfo = (object)$typeInfoArray;
            $properties = [];
            /** @var Column $column */
            foreach ($table->getColumns() as $column) {
                $name = $column->getPropertyName();
                if (isset($this->fields[$name])) {
                    $property = $this->buildAnnotationProperty($column, $type);
                    if(!empty($property)){
                        $properties[] = $property;
                    }
                }
            }
            $propertiesAnnotation = implode(self::PROPERTIES_ANNOTATION_DELIMITER, $properties);
            if(!empty($propertiesAnnotation)){
                $this->annotations[] = '@ApiFilter('.$typeInfo->class.',properties={'.$propertiesAnnotation.'})';
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }
}

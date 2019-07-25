<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2019-07-24
 * Time: 16:21
 */

namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps;

use MwbExporter\Formatter\Doctrine2\Model;

abstract class ApiPlatformFieldAnnotations
{

    /**
     * @param Model\Table $table
     *
     * @return $this
     */
    abstract function buildAnnotations(Model\Table $table);

    const COMMENT_FIELD_DELIMITER = ",";
    const COMMENT_FIELD_PROPERTIES_DELIMITER = ":";
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
        if(empty($comment)) {
            return $this;
        }
        $rawApiFilters = explode(SELF::COMMENT_FIELD_DELIMITER, strtolower(trim($comment)));
        if (count($rawApiFilters) === 0 || $rawApiFilters[0] == "") {
            $this->fields = [];
            return $this;
        }

        foreach($rawApiFilters as $rawFilter) {
            $rawFilterDetails = explode(self::COMMENT_FIELD_PROPERTIES_DELIMITER, trim($rawFilter));
            if (count($rawFilterDetails) > 0 && $rawFilterDetails[0] != "") {
                $name = array_shift($rawFilterDetails);
                $this->fields[$name] =  (object)[
                    'name' => $name,
                    'modifiers' => $rawFilterDetails
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
    public function generateAnnotation(string $fieldName, ?string $typeKey = ""): string
    {
        $typeInfo = (object)$this->typeInfo[$typeKey];
        $properties = '"'.$fieldName.'"';
        if(count($this->fields[$fieldName]->modifiers)) {
            $properties .= $typeInfo->delimiter.'"'.implode('"'.$typeInfo->delimiter.'"',$this->fields[$fieldName]->modifiers).'"';
        } elseif(count($typeInfo->modifiers)) {
            $properties .= $typeInfo->delimiter.'"'.implode('"'.$typeInfo->delimiter.'"',$typeInfo->modifiers).'"';
        }

        return '@ApiFilter('.$typeInfo->class.',properties={'.$properties.'})';
    }


    /**
     * @return array
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }
}

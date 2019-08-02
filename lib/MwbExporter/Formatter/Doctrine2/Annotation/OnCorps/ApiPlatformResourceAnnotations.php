<?php


namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps;


use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;
use MwbExporter\Formatter\Doctrine2\CustomComment;

class ApiPlatformResourceAnnotations
{

    const COMMENT_FIELD_DELIMITER = ":";
    const PROPERTIES_ANNOTATION_DELIMITER = ',';

    protected $annotations = [];
    /**
     * @return array
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    /**
     * @param Model\Table $table
     *
     * @return $this
     */
    public function buildAnnotations(Table $table) : self
    {
        $this->annotations[] = $table->getAnnotation('@ApiResource(', null, [], '');
        $this->annotations[] = '    attributes={';
        $this->annotations[] = '    '.implode(self::PROPERTIES_ANNOTATION_DELIMITER, $this->getPaginationPropertiesAnnotation($table));
        $this->annotations[] = '    }';
        $this->annotations[] = ')';
        return $this;
    }

    public function getPaginationPropertiesAnnotation(Table $table) : array
    {
        $annotation = $table->parseComment(CustomComment::API_PLATFORM_PAGINATION);
        if($$annotation) {
            return [];
        }
        $parts = explode(self::COMMENT_FIELD_DELIMITER, $annotation);
        $rawSetting = strtolower(trim($parts[0]));
        $setting = $rawSetting == 'on' || $rawSetting == 'off' ? $rawSetting : 'on';
        if(count($parts) > 1 &&  filter_var(trim($parts[1]), FILTER_VALIDATE_INT)){
            $itemsPerPage = (int)trim($parts[1]);
        }
        $properties = [];
        $properties[] = '    "pagination_enabled"=' . ($setting === 'on' ? 'true' : 'false');
        if(!is_null($itemsPerPage)) {
            $properties[] = '" maximum_items_per_page"=' . $itemsPerPage;
        };

        return $properties;
    }

}

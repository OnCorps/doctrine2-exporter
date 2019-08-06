<?php


namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps;


use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;
use MwbExporter\Formatter\Doctrine2\CustomComment;

class ApiPlatformResourceAnnotations
{

    const COMMENT_FIELD_ENTRY_DELIMITER = ",";
    const COMMENT_FIELD_VALUE_DELIMITER = ":";
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
        $comment = $table->parseComment(CustomComment::API_PLATFORM_PAGINATION);
        $settings = $this->getKeyValueSettingsFromComment($comment);
        $properties = [];
        if(isset($settings['enabled'])) {
            //Safest approach is to switch off if explicitly set to false - anything else and we leave it on.
            $properties[] = '    "pagination_enabled"=' . ($settings['enabled'] === 'false' ? 'false' : 'true');
        } else {
            //User has put in a pagination comment but not been explicit so safest approach is to assume thay want pagination on.
            $properties[] = '    "pagination_enabled"=true';
        }

        if(isset($settings['items']) &&  filter_var($settings['items'], FILTER_VALIDATE_INT)){
            $itemsPerPage = (int)trim($settings['items']);
            $properties[] = ' "pagination_items_per_page"=' . $itemsPerPage;
        }

        if(isset($settings['max_items']) &&  filter_var($settings['max_items'], FILTER_VALIDATE_INT)){
            $itemsPerPage = (int)trim($settings['max_items']);
            $properties[] = ' "maximum_items_per_page"=' . $itemsPerPage;
        }

        //Only activate client enablement if we can discern the correct intent.
        //If we leave annotation out it will revert to 'safe' base server configuration
        if(isset($settings['client_control'])) {
            $properties[] = ' "pagination_client_enabled"=' . ($settings['client_control'] === 'true' ? 'true' : 'false');
            $properties[] = ' "client_items_per_page"=' . ($settings['client_control'] === 'true' ? 'true' : 'false');
        }

        return $properties;
    }

    private function getKeyValueSettingsFromComment(?string $comment = '')
    {
        $settings = [];
        if(empty($comment)) {
            return $settings;
        }
        $parts = explode(self::COMMENT_FIELD_ENTRY_DELIMITER, $comment);
        foreach($parts as $keyValue) {
            $keyValueParts = explode(self::COMMENT_FIELD_VALUE_DELIMITER, $keyValue);
            if(count($keyValueParts) == 2) {
                $settings[strtolower(trim($keyValueParts[0]))] = strtolower(trim($keyValueParts[1]));
            } else {
                print_r('Comment annotation: '. $comment . ' could not be fully parsed. Key value count mismatch');
            }
        }
        return $settings;
    }

}

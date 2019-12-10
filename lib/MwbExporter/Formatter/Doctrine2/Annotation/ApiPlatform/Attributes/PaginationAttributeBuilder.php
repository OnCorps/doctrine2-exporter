<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Attributes;

use MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\CustomComment;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;

class PaginationAttributeBuilder implements ClassLevelAttributeInterface
{
    const PROPERTIES_ANNOTATION_DELIMITER = ',';

    private $table;

    private $commentSettingGenerator;


    public function __construct(Table $table, CommentSettingGenerator $commentSettingGenerator)
    {
        $this->table = $table;
        $this->commentSettingGenerator = $commentSettingGenerator;
    }

    public function buildAttribute(): ?string
    {
        $comment = $this->table->parseComment(CustomComment::API_PLATFORM_PAGINATION);
        $settings = $this->commentSettingGenerator->getKeyValueSettingsFromComment($comment);

        if(empty($settings)) {
            return null;
        }


        $properties = [];

        if(isset($settings['enabled'])) {
            //Safest approach is to switch off if explicitly set to false - anything else and we leave it on.
            $properties[] = '    "pagination_enabled"=' . ($settings['enabled'] === 'false' ? 'false' : 'true');
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
        $properties = implode(self::PROPERTIES_ANNOTATION_DELIMITER, $properties);


        return $properties;
    }
}

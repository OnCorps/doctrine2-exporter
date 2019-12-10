<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Attributes;

use MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\CustomComment;

class CommentSettingGenerator
{
    const COMMENT_FIELD_ENTRY_DELIMITER = ",";
    const COMMENT_FIELD_VALUE_DELIMITER = ":";

    /**
     * @param string|null $comment
     *
     * @return array
     */
    public function getKeyValueSettingsFromComment(?string $comment = '')
    {
        $settings = [];

        if (empty($comment)) {
            return $settings;
        }

        $parts = explode(self::COMMENT_FIELD_ENTRY_DELIMITER, $comment);
        foreach ($parts as $keyValue) {
            $keyValueParts = explode(self::COMMENT_FIELD_VALUE_DELIMITER, $keyValue);
            if (count($keyValueParts) == 2) {
                $settings[strtolower(trim($keyValueParts[0]))] = strtolower(trim($keyValueParts[1]));
            } else {
                print_r('Comment annotation: ' . $comment . ' could not be fully parsed. Key value count mismatch');
            }
        }

        return $settings;
    }

}

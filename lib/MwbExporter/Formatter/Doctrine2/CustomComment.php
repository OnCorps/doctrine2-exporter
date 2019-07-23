<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2;

/**
 * A dictionary class of what is known in the exporter and therfore can safely be used in mysql workbench as comment
 *
 * Class CustomOnCorpsComment
 * @package MwbExporter\Formatter\Doctrine2
 */
class CustomComment
{
    const PRIMARY_KEY_REQUIRES_EXTERNAL_IMPORT = 'external_id';
}

<?php
declare(strict_types=1);

namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Attributes;

interface ClassLevelAttributeInterface
{
    public function buildAttribute(): ?string;
}

<?php


namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform;

use MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Attributes\ClassLevelAttributeAnnotations;
use MwbExporter\Formatter\Doctrine2\Annotation\Formatter;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;

class ApiPlatformManager
{
    private $include;
    private $table;

    /**
     * ApiPlatformApiResource constructor.
     */
    public function __construct(Table $table)
    {
        $this->include = $table->getConfig()->get(Formatter::CFG_API_PLATFORM_ANNOTATIONS);
        $this->table = $table;
    }

    /**
     * @return array
     */
    public function getApiResourceAnnotations(): array
    {
        if(!$this->include) {
            return [];
        }

        return (new ClassLevelAttributeAnnotations($this->table))->getAnnotations();
    }

    public function getApiFilterAnnotations() {

        if(!$this->include) {
            return [];
        }
        $classes = [
            ApiPlatformSortAnnotations::class => $this->table->parseComment(CustomComment::API_PLATFORM_SORT),
            ApiPlatformSearchAnnotations::class => $this->table->parseComment(CustomComment::API_PLATFORM_SEARCH),
        ];
        $annotations = [];

        foreach($classes as $class => $comment){
            $provider = new $class($this->table);
            $annotations = array_merge(
                $annotations,
                $provider
                    ->processFields($comment)
                    ->buildAnnotations()
                    ->getAnnotations()
            );
        }

        return $annotations;
    }

}

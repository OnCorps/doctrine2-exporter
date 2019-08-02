<?php


namespace MwbExporter\Formatter\Doctrine2\Annotation\OnCorps;

use MwbExporter\Formatter\Doctrine2\Annotation\Formatter;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Column;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;
use MwbExporter\Formatter\Doctrine2\CustomComment;
use MwbExporter\Writer\WriterInterface;

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
        return (new ApiPlatformResourceAnnotations())
            ->buildAnnotations($this->table)
            ->getAnnotations();
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
            $provider = new $class;
            $annotations = array_merge(
                $annotations,
                $provider
                    ->processFields($comment)
                    ->buildAnnotations($this->table)
                    ->getAnnotations()
            );
        }
        return $annotations;
    }

}

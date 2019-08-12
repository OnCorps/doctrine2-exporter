<?php


namespace MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform;


use MwbExporter\Formatter\Doctrine2\Annotation\Model\Column;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table;
use MwbExporter\Writer\WriterInterface;

trait ApiPlatformColumnExtensions
{
    public function writeIdColumnGetter(WriterInterface $writer, string $nativeType)
    {
        $primaryKeyCalledId = false;
        /** @var Column $column */
        $column = $this;
        if($column->isPrimary()) {
            //@TODO - assumes that we only have one primary key column. If multiple then we have an issue...
            if(strtolower($column->getColumnName()) !== 'id') {
                $column->writePrimaryKeyFetcher($writer, $nativeType, 'getId');
            }
        }
    }

    public function writePrimaryKeyFetcher(WriterInterface $writer, string $nativeType, string $getterName = 'fetchId') {
        $columnName = $this->getColumnName();

        $writer->write('/**')
            ->write(' * Get the value of ' . $columnName . '.')
            ->write(' *')
            ->write(' * @return ' . $nativeType)
            ->write(' */')
            ->write('public function ' . $getterName . '()')
            ->write('{')
            ->indent()
            ->write('return $this->' . $columnName . ';')
            ->outdent()
            ->write('}')
            ->write('');
    }
}

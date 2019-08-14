<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2014 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MwbExporter\Formatter\Doctrine2\Annotation\Model;

use Doctrine\Common\Inflector\Inflector;
use MwbExporter\Formatter\Doctrine2\Annotation\Formatter;
use MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform\Assertion\PropertyLevelAssertionBuilderProvider;
use MwbExporter\Formatter\Doctrine2\Model\Column as BaseColumn;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Doctrine2\Annotation\ApiPlatform;

class Column extends BaseColumn
{

    use ApiPlatform\ApiPlatformColumnExtensions;

    private function getStringDefaultValue() {
        $defaultValue = $this->getDefaultValue();
        if (is_null($defaultValue) || 'CURRENT_TIMESTAMP' == $defaultValue) {
            $defaultValue = '';
        } else {
            if ($this->getColumnType() == 'com.mysql.rdbms.mysql.datatype.varchar') {
                $defaultValue = " = '$defaultValue'";
            } elseif ($this->isBoolean()) {
                $defaultValue = " = ".($defaultValue == 0 ? 'false' : 'true');
            } else {
                $defaultValue = " = $defaultValue";
            }
        }
        return $defaultValue;
    }

    public function writeVar(WriterInterface $writer)
    {
        $propertyLevelBuilder = (new PropertyLevelAssertionBuilderProvider())->getPropertyLevelAssertionBuilder();
        $columnAssertions = $propertyLevelBuilder->buildAnnotations($this);

        if (!$this->isIgnored()) {
            $useBehavioralExtensions = $this->getConfig()->get(Formatter::CFG_USE_BEHAVIORAL_EXTENSIONS);
            $isBehavioralColumn = strstr($this->getTable()->getName(), '_img') && $useBehavioralExtensions;
            $comment = $this->getComment();
            $writer
                ->write('/**')
                ->writeIf($comment, $comment)
                ->writeIf($this->isPrimary,
                        ' * '.$this->getTable()->getAnnotation('Id'))
            ;

            if ($columnAssertions) {
                foreach ($columnAssertions as $columnAssertion) {
                    $writer->writeIf($columnAssertion, $columnAssertion);
                }
            }

            if($this->isUuid()) {
                $writer
                    ->write(' * '.$this->getTable()->getAnnotation('Column', ['type' => 'guid', 'unique' => true]))
                ;
            } else {
                $writer
                    ->write(' * '.$this->getTable()->getAnnotation('Column', $this->asAnnotation()));
            }

            $writer
                ->writeIf($useBehavioralExtensions && $this->getColumnName() === 'created_at',
                        ' * @Gedmo\Timestampable(on="create")')
                ->writeIf($useBehavioralExtensions && $this->getColumnName() === 'updated_at',
                        ' * @Gedmo\Timestampable(on="update")')
                ->writeIf($this->isAutoIncrement(),
                        ' * '.$this->getTable()->getAnnotation('GeneratedValue', array('strategy' => strtoupper($this->getConfig()->get(Formatter::CFG_GENERATED_VALUE_STRATEGY)))))
                ->writeIf($isBehavioralColumn && strstr($this->getColumnName(), 'path'),
                        ' * @Gedmo\UploadableFilePath')
                ->writeIf($isBehavioralColumn && strstr($this->getColumnName(), 'name'),
                        ' * @Gedmo\UploadableFileName')
                ->writeIf($isBehavioralColumn && strstr($this->getColumnName(), 'mime'),
                        ' * @Gedmo\UploadableFileMimeType')
                ->writeIf($isBehavioralColumn && strstr($this->getColumnName(), 'size'),
                        ' * @Gedmo\UploadableFileSize')
                ->write(' */')
                ->write('protected $'.$this->getPropertyName().$this->getStringDefaultValue().';')
                ->write('')
            ;
        } 

        return $this;
    }

    /**
     * Explicit function to get the PHP class property name for the column.
     *
     * @return string
     */
    public function getPropertyName()
    {
        return Inflector::camelize($this->getName());
    }

    public function writeGetterAndSetter(WriterInterface $writer)
    {
        if (!$this->isIgnored()) {
            $this->getDocument()->addLog(sprintf('  Writing setter/getter for column "%s"', $this->getColumnName()));

            $table = $this->getTable();
            $converter = $this->getFormatter()->getDatatypeConverter();
            $nativeType = $converter->getNativeType($converter->getMappedType($this));
            $shouldTypehintProperties = $this->getConfig()->get(Formatter::CFG_PROPERTY_TYPEHINT);
            $typehint = $shouldTypehintProperties && class_exists($nativeType) ? "$nativeType " : '';

            if (!$this->isNotNull()) {
                $typehint = $shouldTypehintProperties && class_exists($nativeType) ? "?$nativeType " : '';
            }

            if (!$this->isPrimary || $this->isImportedPrimaryKey()) {
                $this->writeSetter($writer, $nativeType, $table, $typehint);
            }

            if($this->isImportedPrimaryKey()) {
                //Helpful for external tooling and code needed to understand what is the primary key
                //without reling on the assumption that is always called "id"
                $this->writePrimaryKeyFetcher($writer, $nativeType);
            }

            $this->writeIdColumnGetter($writer, $nativeType);

            $this->writeGetter($writer, $nativeType);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function asAnnotation()
    {
        $attributes = array(
            'name' => ($columnName = $this->getTable()->quoteIdentifier($this->getColumnName())) !== $this->getColumnName() ? $columnName : null,
            'type' => $this->getFormatter()->getDatatypeConverter()->getMappedType($this),
        );
        if (($length = $this->parameters->get('length')) && ($length != -1)) {
            $attributes['length'] = (int) $length;
        }
        if (($precision = $this->parameters->get('precision')) && ($precision != -1) && ($scale = $this->parameters->get('scale')) && ($scale != -1)) {
            $attributes['precision'] = (int) $precision;
            $attributes['scale'] = (int) $scale;
        }
        if ($this->isNullableRequired()) {
            $attributes['nullable'] = $this->getNullableValue();
        }

        $attributes['options'] = array();
        if ($this->isUnsigned()) {
            $attributes['options'] = array('unsigned' => true);
        }

        if ('json' === $attributes['type']) {
            $attributes['options']['jsonb'] = true;
        }

        $rawDefaultValue = $this->getDefaultValue();
        if ($rawDefaultValue !== '') {
            $attributes['options']['default'] = $rawDefaultValue === '' ? null : $rawDefaultValue;
        }

        if (count($attributes['options']) == 0) {
            unset($attributes['options']);
        }

        return $attributes;
    }

    /**
     * Checks if column is commented as requiring external id import
     * @return bool
     */
    private function isImportedPrimaryKey(): bool {
        return (bool)(
            $this->parseComment(
                ApiPlatform\CustomComment::PRIMARY_KEY_REQUIRES_EXTERNAL_IMPORT,
                $this->getComment()
            ) === 'true'
            ??
            false
        );
    }

    /**
     * @param WriterInterface $writer
     * @param string $nativeType
     * @param Table $table
     * @param string $typehint
     */
    private function writeSetter(WriterInterface $writer, string $nativeType, Table $table, string $typehint): void {
        $columnName = $this->getPropertyName();
        $writer
            ->write('/**')
            ->write(' * Set the value of ' . $columnName . '.')
            ->write(' *')
            ->write(' * @param ' . $nativeType . ' $' . $columnName)
            ->write(' * @return ' . $table->getNamespace())
            ->write(' */')
            ->write('public function set' . $this->getBeautifiedColumnName() . '(' . $typehint . '$' . $columnName . ')')
            ->write('{')
            ->indent()
            ->write('$this->' . $columnName . ' = $' . $columnName . ';')
            ->write('')
            ->write('return $this;')
            ->outdent()
            ->write('}')
            ->write('');
    }

    /**
     * @param WriterInterface $writer
     * @param string $nativeType
     */
    private function writeGetter(WriterInterface $writer, string $nativeType): void {
        $columnName = $this->getPropertyName();
        $writer->write('/**')
            ->write(' * Get the value of ' . $columnName . '.')
            ->write(' *')
            ->write(' * @return ' . $nativeType)
            ->write(' */')
            ->write('public function get' . $this->getBeautifiedColumnName() . '()')
            ->write('{')
            ->indent()
            ->write('return $this->' . $columnName . ';')
            ->outdent()
            ->write('}')
            ->write('');
    }
}

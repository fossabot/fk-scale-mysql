<?php

/**
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Daniel Popiniuc
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace danielgp\fk_scale_mysql;

/**
 * Description of FKchange
 *
 * @author Daniel Popiniuc <danielpopiniuc@gmail.com>
 */
class FKchange
{

    use configurationMySQL,
        configurationForAction,
        \danielgp\common_lib\CommonCode,
        \danielgp\common_lib\MySQLiAdvancedOutput;

    public function __construct()
    {
        $mysqlConfig           = $this->configuredMySqlServer();
        $this->connectToMySql($mysqlConfig);
        $elToModify            = $this->targetElementsToModify();
        echo '<h1>Database "' . $elToModify['Database'] . '", table "' . $elToModify['Table']
        . '", column "' . $elToModify['Column'] . '" scale to "' . $elToModify['NewDataType']
        . '" on "' . $mysqlConfig['verbose'] . '"</h1>';
        $colToIdentify         = [
            'TABLE_SCHEMA' => $elToModify['Database'],
            'TABLE_NAME'   => $elToModify['Table'],
            'COLUMN_NAME'  => $elToModify['Column'],
        ];
        $col                   = $this->getMySQLlistColumns($colToIdentify);
        $additionalFeatures    = [
            'REFERENCED_TABLE_SCHEMA' => $elToModify['Database'],
            'REFERENCED_TABLE_NAME'   => $elToModify['Table'],
            'REFERENCED_COLUMN_NAME'  => $elToModify['Column'],
            'REFERENCED_TABLE_NAME'   => 'NOT NULL',
        ];
        $q                     = $this->sQueryMySqlIndexes($additionalFeatures);
        $targetTableTextFields = $this->setMySQLquery2Server($q, 'full_array_key_numbered')['result'];
        if (is_array($targetTableTextFields)) {
            foreach ($targetTableTextFields as $key => $value) {
                echo $this->createDropForeignKey([
                    'Database'       => $value['TABLE_SCHEMA'],
                    'Table'          => $value['TABLE_NAME'],
                    'ForeignKeyName' => $value['CONSTRAINT_NAME'],
                ]);
                $colDetails[$key] = $this->getMySQLlistColumns([
                    'TABLE_SCHEMA' => $value['TABLE_SCHEMA'],
                    'TABLE_NAME'   => $value['TABLE_NAME'],
                    'COLUMN_NAME'  => $value['COLUMN_NAME'],
                ]);
            }
            $mainColArray = [
                'Database'    => $value['REFERENCED_TABLE_SCHEMA'],
                'Table'       => $value['REFERENCED_TABLE_NAME'],
                'Column'      => $value['REFERENCED_COLUMN_NAME'],
                'NewDataType' => $elToModify['NewDataType'],
                'IsNullable'  => $col[0]['IS_NULLABLE'],
                'Default'     => $col[0]['COLUMN_DEFAULT'],
                'Extra'       => $col[0]['EXTRA'],
                'Comment'     => $col[0]['COLUMN_COMMENT'],
            ];
            echo $this->createChangeChangeColumn($mainColArray, 'color:blue;font-weight:bold;');
            foreach ($targetTableTextFields as $key => $value) {
                echo $this->createChangeChangeColumn([
                    'Database'    => $value['TABLE_SCHEMA'],
                    'Table'       => $value['TABLE_NAME'],
                    'Column'      => $value['COLUMN_NAME'],
                    'NewDataType' => $elToModify['NewDataType'],
                    'IsNullable'  => $colDetails[$key][0]['IS_NULLABLE'],
                    'Default'     => $colDetails[$key][0]['COLUMN_DEFAULT'],
                    'Extra'       => $colDetails[$key][0]['EXTRA'],
                    'Comment'     => $colDetails[$key][0]['COLUMN_COMMENT'],
                ]);
                echo $this->createForeignKey([
                    'Database'           => $value['TABLE_SCHEMA'],
                    'Table'              => $value['TABLE_NAME'],
                    'Column'             => $value['COLUMN_NAME'],
                    'ForeignKeyName'     => $value['CONSTRAINT_NAME'],
                    'ReferencedDatabase' => $value['REFERENCED_TABLE_SCHEMA'],
                    'ReferencedTable'    => $value['REFERENCED_TABLE_NAME'],
                    'ReferencedColumn'   => $value['REFERENCED_COLUMN_NAME'],
                    'RuleDelete'         => $value['DELETE_RULE'],
                    'RuleUpdate'         => $value['UPDATE_RULE'],
                ]);
            }
            echo '<hr/>';
        }
    }

    private function createChangeChangeColumn($parameters, $style = null)
    {
        return '<div style="' . (is_null($style) ? 'color:blue;' : $style) . '">'
                . 'ALTER TABLE `' . $parameters['Database'] . '`.`' . $parameters['Table']
                . '` CHANGE `' . $parameters['Column'] . '` `' . $parameters['Column'] . '` '
                . $parameters['NewDataType'] . ' '
                . $this->setColumnDefinitionAdditional($parameters['IsNullable'], $parameters['Default'])
                . (strlen($parameters['Extra']) > 0 ? ' AUTO_INCREMENT' : '')
                . (strlen($parameters['Comment']) > 0 ? ' COMMENT "' . $parameters['Comment'] . '"' : '')
                . ';'
                . '</div>';
    }

    private function createDropForeignKey($parameters)
    {
        return '<div style="color:red;">'
                . 'ALTER TABLE `' . $parameters['Database'] . '`.`' . $parameters['Table']
                . '` DROP FOREIGN KEY `' . $parameters['ForeignKeyName']
                . '`;'
                . '</div>';
    }

    private function createForeignKey($parameters)
    {
        return '<div style="color:green;">'
                . 'ALTER TABLE `' . $parameters['Database'] . '`.`' . $parameters['Table']
                . '` ADD CONSTRAINT `' . $parameters['ForeignKeyName'] . '` FOREIGN KEY (`'
                . $parameters['Column'] . '`) REFERENCES `' . $parameters['ReferencedDatabase'] . '`.`'
                . $parameters['ReferencedTable'] . '` (`' . $parameters['ReferencedColumn'] . '`) '
                . 'ON DELETE '
                . ($parameters['RuleDelete'] == 'NULL' ? 'SET NULL' : $parameters['RuleDelete']) . ' '
                . 'ON UPDATE '
                . ($parameters['RuleUpdate'] == 'NULL' ? 'SET NULL' : $parameters['RuleUpdate'])
                . ';'
                . '</div>';
    }

    private function setColumnDefinitionAdditional($nullableYesNo, $defaultValue)
    {
        switch ($nullableYesNo) {
            case 'NO':
                if ($defaultValue === null) {
                    $columnDefinitionAdditional = 'NOT NULL';
                } else {
                    $columnDefinitionAdditional = 'NOT NULL DEFAULT "' . $defaultValue . '"';
                }
                break;
            case 'YES':
                if ($defaultValue === null) {
                    $columnDefinitionAdditional = 'DEFAULT NULL';
                } else {
                    $columnDefinitionAdditional = 'DEFAULT "' . $defaultValue . '"';
                }
                break;
        }
        return $columnDefinitionAdditional;
    }
}

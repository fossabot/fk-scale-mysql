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

    use ConfigurationMySQL,
        ConfigurationForAction,
        \danielgp\common_lib\CommonCode,
        \danielgp\common_lib\MySQLiAdvancedOutput;

    private $applicationSpecificArray;

    public function __construct()
    {
        echo $this->setHeaderCommon([
            'css'        => [
                'css/fk_scale_mysql.css',
            ],
            'javascript' => [
                'vendor/danielgp/common-lib/js/tabber/tabber-management.min.js',
                'vendor/danielgp/common-lib/js/tabber/tabber.min.js',
            ],
        ]);
        $mysqlConfig           = $this->configuredMySqlServer();
        echo $this->setTitle();
        $elToModify            = $this->targetElementsToModify();
        echo '<div class="tabber" id="tabberFKscaleMySQL">';
        echo '<div class="tabbertab" id="FKscaleMySQLparameters" title="Parameters for scaling">'
        . $this->setInputFormForFKscaling($mysqlConfig)
        . '</div><!-- end of Parameters tab -->';
        $mConnection           = $this->connectToMySql($mysqlConfig);
        $targetTableTextFields = $this->getForeignKeys($elToModify);
        echo '<div class="tabbertab" id="FKscaleMySQLresults" title="Results">';
        if (is_array($targetTableTextFields)) {
            echo $this->createDropForignKeysAndGetTargetColumnDefinition($targetTableTextFields);
            $mainColArray = $this->packParameteresForMainChangeColumn($elToModify, $targetTableTextFields);
            echo $this->createChangeColumn($mainColArray, [
                'style'                => 'color:blue;font-weight:bold;',
                'includeOldColumnType' => true,
            ]);
            echo $this->recreateFKs($elToModify, $targetTableTextFields);
        } else {
            if (strlen($mConnection) === 0) {
                echo '<p style="color:red;">Check if provided parameters are correct '
                . 'as the combination of Database. Table and Column name were not found as a Foreign Key!</p>';
            } else {
                echo '<p style="color:red;">Check your "configurationMySQL.php" file '
                . 'for correct MySQL connection parameters '
                . 'as the current ones were not able to be used to establish a connection!</p>';
            }
        }
        echo '</div><!-- end of FKscaleMySQLresults tab -->'
        . '</div><!-- tabberFKscaleMySQL -->';
    }

    public function __destruct()
    {
        echo $this->setFooterCommon();
    }

    private function createChangeColumn($parameters, $aditionalFeatures = null)
    {
        return '<div style="'
                . (isset($aditionalFeatures['style']) ? $aditionalFeatures['style'] : 'color:blue;')
                . '">'
                . 'ALTER TABLE `' . $parameters['Database'] . '`.`' . $parameters['Table']
                . '` CHANGE `' . $parameters['Column'] . '` `' . $parameters['Column'] . '` '
                . $parameters['NewDataType'] . ' '
                . $this->setColumnDefinitionAdditional($parameters['IsNullable'], $parameters['Default'])
                . (strlen($parameters['Extra']) > 0 ? ' AUTO_INCREMENT' : '')
                . (strlen($parameters['Comment']) > 0 ? ' COMMENT "' . $parameters['Comment'] . '"' : '')
                . ';'
                . (isset($aditionalFeatures['includeOldColumnType']) ? ' /* from '
                        . $parameters['OldDataType'] . ' */' : '')
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

    private function createDropForignKeysAndGetTargetColumnDefinition($targetTableTextFields)
    {
        $sReturn = [];
        foreach ($targetTableTextFields as $key => $value) {
            $sReturn[]                                    = $this->createDropForeignKey([
                'Database'       => $value['TABLE_SCHEMA'],
                'Table'          => $value['TABLE_NAME'],
                'ForeignKeyName' => $value['CONSTRAINT_NAME'],
            ]);
            $this->applicationSpecificArray['Cols'][$key] = $this->getMySQLlistColumns([
                'TABLE_SCHEMA' => $value['TABLE_SCHEMA'],
                'TABLE_NAME'   => $value['TABLE_NAME'],
                'COLUMN_NAME'  => $value['COLUMN_NAME'],
            ]);
        }
        return implode('', $sReturn);
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

    private function getForeignKeys($elToModify)
    {
        $additionalFeatures = [
            'REFERENCED_TABLE_SCHEMA' => $elToModify['Database'],
            'REFERENCED_TABLE_NAME'   => $elToModify['Table'],
            'REFERENCED_COLUMN_NAME'  => $elToModify['Column'],
            'REFERENCED_TABLE_NAME'   => 'NOT NULL',
        ];
        $q                  = $this->sQueryMySqlIndexes($additionalFeatures);
        return $this->setMySQLquery2Server($q, 'full_array_key_numbered')['result'];
    }

    private function packParameteresForMainChangeColumn($elToModify, $targetTableTextFields, $col)
    {
        $colToIdentify = [
            'TABLE_SCHEMA' => $elToModify['Database'],
            'TABLE_NAME'   => $elToModify['Table'],
            'COLUMN_NAME'  => $elToModify['Column'],
        ];
        $col           = $this->getMySQLlistColumns($colToIdentify);
        return [
            'Database'    => $targetTableTextFields[0]['REFERENCED_TABLE_SCHEMA'],
            'Table'       => $targetTableTextFields[0]['REFERENCED_TABLE_NAME'],
            'Column'      => $targetTableTextFields[0]['REFERENCED_COLUMN_NAME'],
            'OldDataType' => strtoupper($col[0]['COLUMN_TYPE']) . ' '
            . $this->setColumnDefinitionAdditional($col[0]['IS_NULLABLE'], $col[0]['COLUMN_DEFAULT']),
            'NewDataType' => $elToModify['NewDataType'],
            'IsNullable'  => $this->applicationSpecificArray['Cols'][0]['IS_NULLABLE'],
            'Default'     => $this->applicationSpecificArray['Cols'][0]['COLUMN_DEFAULT'],
            'Extra'       => $this->applicationSpecificArray['Cols'][0]['EXTRA'],
            'Comment'     => $this->applicationSpecificArray['Cols'][0]['COLUMN_COMMENT'],
        ];
    }

    private function recreateFKs($elToModify, $targetTableTextFields, $colDetails)
    {
        $sReturn = [];
        foreach ($targetTableTextFields as $key => $value) {
            $sReturn[] = $this->createChangeColumn([
                'Database'    => $value['TABLE_SCHEMA'],
                'Table'       => $value['TABLE_NAME'],
                'Column'      => $value['COLUMN_NAME'],
                'NewDataType' => $elToModify['NewDataType'],
                'IsNullable'  => $this->applicationSpecificArray['Cols'][$key][0]['IS_NULLABLE'],
                'Default'     => $this->applicationSpecificArray['Cols'][$key][0]['COLUMN_DEFAULT'],
                'Extra'       => $this->applicationSpecificArray['Cols'][$key][0]['EXTRA'],
                'Comment'     => $this->applicationSpecificArray['Cols'][$key][0]['COLUMN_COMMENT'],
            ]);
            $sReturn[] = $this->createForeignKey([
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
        return implode('', $sReturn);
    }

    private function returnInputsCleaned($inputFieldName)
    {
        $sReturn = '';
        if (isset($_REQUEST[$inputFieldName])) {
            $sReturn = 'value="' . filter_var($_REQUEST[$inputFieldName], FILTER_SANITIZE_STRING) . '" ';
        }
        return $sReturn;
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

    private function setInputFormForFKscaling($mysqlConfig)
    {
        $sReturn             = [];
        $sReturn[]           = '<label for="dbName">Database name to analyze:</label>'
                . '<input type="text" id="dbName" name="db" placeholder="database name" '
                . $this->returnInputsCleaned('db')
                . 'size="30" maxlength="64" class="labell" />';
        $sReturn[]           = '<label for="tblName">Table name to analyze:</label>'
                . '<input type="text" id="tblName" name="tbl" placeholder="table name" '
                . $this->returnInputsCleaned('tbl')
                . ' size="30" maxlength="64" class="labell" />';
        $sReturn[]           = '<label for="fldName">Field name to analyze:</label>'
                . '<input type="text" id="fldName" name="fld" placeholder="field name" '
                . $this->returnInputsCleaned('fld')
                . ' size="30" maxlength="64" class="labell" />';
        $sReturn[]           = '<label for="dataType">Data type to change to:</label>'
                . '<input type="text" id="dataType" name="dt" placeholder="valid data type" '
                . $this->returnInputsCleaned('dt')
                . ' size="30" maxlength="64" class="labell" />';
        $sReturn[]           = '<input type="submit" value="Generate SQL queries for scaling" />';
        $styleForMySQLparams = 'color:green;font-weight:bold;font-style:italic;';
        $sReturn[]           = '<p>For security reasons the MySQL connection details are not available '
                . 'to be set/modified through the interface and must be set directly '
                . 'into the "configurationMySQL.php" file. Currently these settings are:<ul>'
                . '<li>Host name where MySQL server resides: <span style="' . $styleForMySQLparams . '">'
                . $mysqlConfig['host'] . '</span></li>'
                . '<li>MySQL port used: <span style="' . $styleForMySQLparams . '">'
                . $mysqlConfig['port'] . '</span></li>'
                . '<li>MySQL database to connect to: <span style="' . $styleForMySQLparams . '">'
                . $mysqlConfig['database'] . '</span></li>'
                . '<li>MySQL username used: <span style="' . $styleForMySQLparams . '">'
                . $mysqlConfig['username'] . '</span></li>'
                . '<li>MySQL password used: <span style="' . $styleForMySQLparams . '">'
                . 'cannot be disclosed due to security reasons</span></li>'
                . '</ul></p>';
        return '<form method="get" action="' . $_REQUEST['PHP_SELF'] . '">'
                . implode('<br/>', $sReturn)
                . '</form>';
    }

    private function setTitle()
    {
        $sReturn   = [];
        $sReturn[] = '<h1>Forign Keys Scale in MySQL</h1>';
        return implode('', $sReturn);
    }
}

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
        FKinterface,
        \danielgp\common_lib\MySQLiAdvancedOutput;

    private $applicationSpecificArray;

    public function __construct()
    {
        $rqst = new \Symfony\Component\HttpFoundation\Request;
        echo $this->setApplicationHeader()
        . $this->buildApplicationInterface($rqst->createFromGlobals())
        . $this->setApplicationFooter();
    }

    private function buildApplicationInterface($sGb)
    {
        $mysqlConfig          = $this->configuredMySqlServer();
        $elToModify           = $this->targetElementsToModify($sGb);
        $transmitedParameters = $this->countTransmitedParameters(['db', 'tbl', 'fld', 'dt']);
        $mConnection          = $this->connectToMySql($mysqlConfig);
        $sReturn              = [];
        $sReturn[]            = $this->buildInputFormTab($mysqlConfig, $transmitedParameters, $sGb);
        $sReturn[]            = $this->buildResultsTab($mConnection, $elToModify, $transmitedParameters);
        return implode('', $sReturn);
    }

    private function buildResultsTab($mConnection, $elToModify, $tParams)
    {
        $sReturn             = [];
        $targetTableTextFlds = $this->getForeignKeys($elToModify);
        $sReturn[]           = '<div class="tabbertab' . ($tParams ? ' tabbertabdefault' : '')
                . '" id="FKscaleMySQLresults" title="Results">';
        if (is_array($targetTableTextFlds)) {
            $sReturn[]    = $this->createDropForeignKeysAndGetTargetColumnDefinition($targetTableTextFlds);
            $mainColArray = $this->packParameteresForMainChangeColumn($elToModify, $targetTableTextFlds);
            $sReturn[]    = $this->createChangeColumn($mainColArray, [
                'style'                => 'color:blue;font-weight:bold;',
                'includeOldColumnType' => true,
            ]);
            $sReturn[]    = $this->recreateFKs($elToModify, $targetTableTextFlds);
        } else {
            $sReturn[] = $this->returnMessagesInCaseOfNoResults($mConnection);
        }
        $sReturn[] = '</div><!-- end of FKscaleMySQLresults tab -->'
                . '</div><!-- tabberFKscaleMySQL -->';
        return implode('', $sReturn);
    }

    private function createChangeColumn($params, $adtnlFeatures = null)
    {
        return '<div style="' . (isset($adtnlFeatures['style']) ? $adtnlFeatures['style'] : 'color:blue;') . '">'
                . 'ALTER TABLE `' . $params['Database'] . '`.`' . $params['Table'] . '` '
                . 'CHANGE `' . $params['Column'] . '` `' . $params['Column'] . '` ' . $params['NewDataType'] . ' '
                . $this->setColumnDefinition($params) . ';'
                . (isset($adtnlFeatures['includeOldColumnType']) ? ' /* from ' . $params['OldDataType'] . ' */' : '')
                . '</div>';
    }

    private function createDropForeignKey($parameters)
    {
        return '<div style="color:red;">'
                . 'ALTER TABLE `' . $parameters['Database'] . '`.`' . $parameters['Table']
                . '` DROP FOREIGN KEY `' . $parameters['ForeignKeyName'] . '`;'
                . '</div>';
    }

    private function createDropForeignKeysAndGetTargetColumnDefinition($targetTableTextFlds)
    {
        $sReturn = [];
        foreach ($targetTableTextFlds as $key => $value) {
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

    private function createForeignKey($params)
    {
        return '<div style="color:green;">'
                . 'ALTER TABLE `' . $params['Database'] . '`.`' . $params['Table'] . '` '
                . 'ADD CONSTRAINT `' . $params['ForeignKeyName'] . '` '
                . 'FOREIGN KEY (`' . $params['Column'] . '`) REFERENCES `' . $params['ReferencedDatabase'] . '`.`'
                . $params['ReferencedTable'] . '` (`' . $params['ReferencedColumn'] . '`) '
                . 'ON DELETE ' . ($params['RuleDelete'] == 'NULL' ? 'SET NULL' : $params['RuleDelete']) . ' '
                . 'ON UPDATE ' . ($params['RuleUpdate'] == 'NULL' ? 'SET NULL' : $params['RuleUpdate']) . ';'
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
        $query              = $this->sQueryMySqlIndexes($additionalFeatures);
        return $this->setMySQLquery2Server($query, 'full_array_key_numbered')['result'];
    }

    private function packParameteresForMainChangeColumn($elToModify, $targetTableTextFlds)
    {
        $colToIdentify = [
            'TABLE_SCHEMA' => $elToModify['Database'],
            'TABLE_NAME'   => $elToModify['Table'],
            'COLUMN_NAME'  => $elToModify['Column'],
        ];
        $col           = $this->getMySQLlistColumns($colToIdentify);
        return [
            'Database'       => $targetTableTextFlds[0]['REFERENCED_TABLE_SCHEMA'],
            'Table'          => $targetTableTextFlds[0]['REFERENCED_TABLE_NAME'],
            'Column'         => $targetTableTextFlds[0]['REFERENCED_COLUMN_NAME'],
            'OldDataType'    => strtoupper($col[0]['COLUMN_TYPE']) . ' ' . $this->setColumnDefinition($col[0]),
            'NewDataType'    => $elToModify['NewDataType'],
            'IS_NULLABLE'    => $col[0]['IS_NULLABLE'],
            'COLUMN_DEFAULT' => $col[0]['COLUMN_DEFAULT'],
            'EXTRA'          => $col[0]['EXTRA'],
            'COLUMN_COMMENT' => $col[0]['COLUMN_COMMENT'],
        ];
    }

    private function recreateFKs($elToModify, $targetTableTextFlds)
    {
        $sReturn = [];
        foreach ($targetTableTextFlds as $key => $value) {
            $sReturn[] = $this->createChangeColumn([
                'Database'       => $value['TABLE_SCHEMA'],
                'Table'          => $value['TABLE_NAME'],
                'Column'         => $value['COLUMN_NAME'],
                'NewDataType'    => $elToModify['NewDataType'],
                'IS_NULLABLE'    => $this->applicationSpecificArray['Cols'][$key][0]['IS_NULLABLE'],
                'COLUMN_DEFAULT' => $this->applicationSpecificArray['Cols'][$key][0]['COLUMN_DEFAULT'],
                'EXTRA'          => $this->applicationSpecificArray['Cols'][$key][0]['EXTRA'],
                'COLUMN_COMMENT' => $this->applicationSpecificArray['Cols'][$key][0]['COLUMN_COMMENT'],
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

    private function setColumnDefinition($inArray)
    {
        $colDefinition = $this->setColumnDefinitionPrefix($inArray['IS_NULLABLE'], $inArray['COLUMN_DEFAULT']);
        if ($inArray['EXTRA'] == 'auto_increment') {
            $colDefinition .= ' AUTO_INCREMENT';
        }
        if (strlen($inArray['COLUMN_COMMENT']) > 0) {
            $colDefinition .= ' COMMENT "' . $inArray['COLUMN_COMMENT'] . '"';
        }
        return $colDefinition;
    }

    private function setColumnDefinitionPrefix($nullableYesNo, $defaultValue)
    {
        $colDefinition = 'NOT NULL DEFAULT "' . $defaultValue . '"';
        if ($nullableYesNo == 'NO') {
            if (is_null($defaultValue)) {
                $colDefinition = 'NOT NULL';
            }
        } elseif ($nullableYesNo == 'YES') {
            $colDefinition = 'DEFAULT "' . $defaultValue . '"';
            if ($defaultValue === null) {
                $colDefinition = 'DEFAULT NULL';
            }
        }
        return $colDefinition;
    }
}

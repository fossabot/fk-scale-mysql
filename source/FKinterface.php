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
trait FKinterface
{

    use \danielgp\common_lib\CommonCode;

    private function buildInputFormForFKscaling($mysqlConfig, \Symfony\Component\HttpFoundation\Request $sGb)
    {
        $sReturn   = [];
        $sReturn[] = $this->buildInputs(['field' => 'db', 'label' => 'Database name to analyze'], $sGb);
        $sReturn[] = $this->buildInputs(['field' => 'tbl', 'label' => 'Table name to analyze'], $sGb);
        $sReturn[] = $this->buildInputs(['field' => 'fld', 'label' => 'Field name to analyze'], $sGb);
        $sReturn[] = $this->buildInputs(['field' => 'dt', 'label' => 'Data type to change to'], $sGb);
        $sReturn[] = '<input type="submit" value="Generate SQL queries for scaling" />';
        $sReturn[] = $this->displayMySqlConfiguration($mysqlConfig);
        return '<form method="get" action="' . filter_var($sGb->server->get('PHP_SELF'), FILTER_SANITIZE_URL) . '">'
                . implode('<br/>', $sReturn)
                . '</form>';
    }

    protected function buildInputFormTab($mysqlConfig, $tParams, \Symfony\Component\HttpFoundation\Request $sGb)
    {
        return '<div class="tabber" id="tabberFKscaleMySQL">'
                . '<div class="tabbertab' . ($tParams ? '' : ' tabbertabdefault')
                . '" id="FKscaleMySQLparameters" title="Parameters for scaling">'
                . $this->buildInputFormForFKscaling($mysqlConfig, $sGb)
                . '</div><!-- end of Parameters tab -->';
    }

    private function buildInputs($inArray, $sGb)
    {
        return '<label for="' . $inArray['field'] . 'Name">' . $inArray['label'] . ':</label>'
                . '<input type="text" id="' . $inArray['field'] . 'Name" name="' . $inArray['field']
                . '" placeholder="' . explode(' ', $inArray['label'])[0] . ' name" '
                . $this->returnInputsCleaned($inArray['field'], $sGb)
                . 'size="30" maxlength="64" class="labell" />';
    }

    private function displayMySqlConfiguration($mysqlConfig)
    {
        $styleForMySQLparams = 'color:green;font-weight:bold;font-style:italic;';
        return '<p>For security reasons the MySQL connection details are not available '
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
    }

    private function returnInputsCleaned($inputFieldName, $sGb)
    {
        $sReturn = '';
        if (!is_null($sGb->get($inputFieldName))) {
            $sReturn = 'value="' . filter_var($sGb->get($inputFieldName), FILTER_SANITIZE_STRING) . '" ';
        }
        return $sReturn;
    }

    protected function returnMessagesInCaseOfNoResults($mConnection)
    {
        if (strlen($mConnection) === 0) {
            return '<p style="color:red;">Check if provided parameters are correct '
                    . 'as the combination of Database. Table and Column name were not found as a Foreign Key!</p>';
        } else {
            return '<p style="color:red;">Check your "configurationMySQL.php" file '
                    . 'for correct MySQL connection parameters '
                    . 'as the current ones were not able to be used to establish a connection!</p>';
        }
    }

    protected function setApplicationFooter()
    {
        return $this->setFooterCommon();
    }

    protected function setApplicationHeader()
    {
        $pageTitle   = 'Foreign Keys Scale in MySQL';
        $headerArray = [
            'css'        => [
                'css/fk_scale_mysql.css',
            ],
            'javascript' => [
                'vendor/danielgp/common-lib/js/tabber/tabber-management.min.js',
                'vendor/danielgp/common-lib/js/tabber/tabber.min.js',
            ],
            'lang'       => 'en-US',
            'title'      => $pageTitle,
        ];
        return $this->setHeaderCommon($headerArray)
                . '<h1>' . $pageTitle . '</h1>';
    }
}

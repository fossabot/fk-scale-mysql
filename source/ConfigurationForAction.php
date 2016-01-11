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

trait ConfigurationForAction
{

    private $superGlobals;

    /**
     * Manages the configuration for parameters to scale FK
     *
     * Default values are targeting "world" database
     * which can be downloaded from http://dev.mysql.com/doc/index-other.html
     *
     * @return array
     */
    protected function targetElementsToModify($sGb)
    {
        $this->superGlobals = $sGb;
        return [
            'Database'    => $this->manageInputWithDefaults([
                'InputName'    => 'db',
                'DefaultValue' => 'world',
            ]),
            'Table'       => $this->manageInputWithDefaults([
                'InputName'    => 'tbl',
                'DefaultValue' => 'country',
            ]),
            'Column'      => $this->manageInputWithDefaults([
                'InputName'    => 'fld',
                'DefaultValue' => 'Code',
            ]),
            'NewDataType' => $this->manageInputWithDefaults([
                'InputName'    => 'dt',
                'DefaultValue' => 'CHAR(6)',
            ]),
        ];
    }

    private function manageInputWithDefaults($inArray)
    {
        if (is_null($this->superGlobals->get($inArray['InputName']))) {
            $this->superGlobals->request->set($inArray['InputName'], $inArray['DefaultValue']);
        }
        return filter_var($this->superGlobals->get($inArray['InputName']), FILTER_SANITIZE_STRING);
    }

    protected function countTransmitedParameters($inParameters)
    {
        $transmited = 0;
        foreach ($inParameters as $parameterName) {
            if (!is_null($this->superGlobals->get($parameterName))) {
                $transmited++;
            }
        }
        return (count($inParameters) === $transmited);
    }
}

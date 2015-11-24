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

    protected function targetElementsToModify()
    {
        if (!isset($_REQUEST['db'])) {
            $_REQUEST['db'] = 'usefull_security';
        }
        if (!isset($_REQUEST['tbl'])) {
            $_REQUEST['tbl'] = 'user_application';
        }
        if (!isset($_REQUEST['fld'])) {
            $_REQUEST['fld'] = 'ApplicationId';
        }
        if (!isset($_REQUEST['dt'])) {
            $_REQUEST['dt'] = 'SMALLINT(5) UNSIGNED';
        }
        return [
            'Database'    => filter_var($_REQUEST['db'], FILTER_SANITIZE_STRING),
            'Table'       => filter_var($_REQUEST['tbl'], FILTER_SANITIZE_STRING),
            'Column'      => filter_var($_REQUEST['fld'], FILTER_SANITIZE_STRING),
            'NewDataType' => filter_var($_REQUEST['dt'], FILTER_SANITIZE_STRING),
        ];
    }
}

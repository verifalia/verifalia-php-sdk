<?php

/*
 * MIT License
 *
 * Copyright (c) 2005-2024 Cobisi Research - https://verifalia.com/
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
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Verifalia\EmailValidations {

    /**
     * Represents the output formats which Verifalia can accept while exporting email validation entries.
     * @see FileValidationRequest
     */
    class ExportedEntriesFormat
    {
        /**
         * Comma-separated values (.csv).
         */
        const CSV = 'text/csv';

        /**
         * Microsoft Excel 97-2003 Worksheet (.xls).
         */
        const EXCEL_XLS = 'application/vnd.ms-excel';

        /**
         * Microsoft Excel workbook (.xslx).
         */
        const EXCEL_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }
}
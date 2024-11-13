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

    use Verifalia\Exceptions\VerifaliaException;

    /**
     * Represents an email validation request of a file to be submitted against the Verifalia API.
     */
    class FileValidationRequest extends ValidationRequestBase
	{
        /**
         * @var resource The file stream which will be submitted for validation.
         */
		public $file;

        /**
         * @var string The media type of the file stream data.
         */
        public $contentType;

        /**
         * @var ?int An optional, zero-based index of the first row to import and process. If not specified, Verifalia will start
         * processing files from the first (0) row.
         */
        public $startingRow;

        /**
         * @var ?int An optional, zero-based index of the last row to import and process. If not specified, Verifalia will process
         * rows until the end of the file.
         */
        public $endingRow;

        /**
         * @var ?int An optional zero-based index of the column to import; applies to comma-separated (.csv), tab-separated (.tsv)
         * and other delimiter-separated values files, and Excel files. If not specified, Verifalia will use the first
         * (0) column.
         */
        public $column;

        /**
         * @var ?int An optional zero-based index of the worksheet to import; applies to Excel files only. If not specified,
         * Verifalia will use the first (0) worksheet.
         */
        public $sheet;

        /**
         * @var ?string Allows to specify the line ending sequence of the provided file; applies to plain-text files, comma-separated
         * (.csv), tab-separated (.tsv) and other delimiter-separated values files.
         * @see LineEndingMode for a list of the supported line endings.
         */
        public $lineEnding;

        /**
         * @var ?string An optional string with the column delimiter sequence of the file; applies to comma-separated (.csv),
         * tab-separated (.tsv) and other delimiter-separated values files. If not specified, Verifalia will use the ","
         * (comma) symbol for CSV files and the "\t" (tab) symbol for TSV files.
         */
        public $delimiter;

        /**
         * Initializes a `FileValidationRequest` to be submitted to the Verifalia email validation engine.
         * @param resource|string $file The name of the file to be submitted for validation, e.g. `./my-list.csv`. You can also specify a
         * resource stream; however, be cautious: due to a bug in the Guzzle library, the stream will be automatically
         * closed once the submission is completed. See https://github.com/guzzle/guzzle/issues/2400#issuecomment-554902119
         * @param string|null $contentType The content media type of the file stream data, e.g. `text/csv`. If you omit this value, the
         * library attempts to guess it based on the extension of the provided file name, if any.
         */
		public function __construct($file, string $contentType = null)
		{
            $this->contentType = $contentType;

            if (is_string($file)) {
                $this->file = fopen($file, 'rb');

                // Guess the content type, if no value has been specified

                if ($contentType == null) {
                    $this->contentType = $this->tryGuessContentTypeFromFileExtension(pathinfo($file, PATHINFO_EXTENSION));
                }
            }
            else {
                $this->file = $file;
            }

            if ($this->contentType == null) {
                throw new VerifaliaException("Can't determine the MIME content type of the file from its extension: specify the content type manually, please.");
            }
        }

        private function tryGuessContentTypeFromFileExtension(string $extension)
        {
            switch ($extension)
            {
                case "txt": return "text/plain";
                case "csv": return "text/csv";
                case "tsv":
                case "tab": return "text/tab-separated-values";
                case "xls": return "application/vnd.ms-excel";
                case "xlsx": return "application/vnd.openxmlformats-officedocument.spreadsheetml";
                default: return null;
            }
        }
	}
}
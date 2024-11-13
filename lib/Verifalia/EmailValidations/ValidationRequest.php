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

    use DateInterval;
    use InvalidArgumentException;

    /**
     * Represents an email validation request to be submitted against the Verifalia API.
     */
    class ValidationRequest extends ValidationRequestBase
	{
        /**
         * @var ValidationRequestEntry[] One or more `ValidationRequestEntry` containing the email addresses to validate.
         */
		public $entries;

        /**
         * Initializes a `ValidationRequest` to be submitted to the Verifalia email validation engine.
         * @param string|string[]|ValidationRequestEntry[]|ValidationRequestEntry $entries Represents one or more
         * entries to be validated. An entry can be either a `string` containing the email address to be validated or a
         * complete `ValidationRequestEntry` instance. To validate multiple entries, provide an array containing either
         * `string` or `ValidationRequestEntry` instances.
         * @see ValidationRequestEntry
         */
		public function __construct($entries)
		{
			$this->entries = array();

			if (is_array($entries)) {
				for ($x = 0; $x < count($entries); $x++) {
					$this->addEntry($entries[$x]);
				}
			} else {
				$this->addEntry($entries);
			}
		}

        /**
         * @param string|ValidationRequestEntry $entry
         * @return void
         */
		private function addEntry($entry)
		{
			if (is_string($entry)) {
				$this->entries[] = new ValidationRequestEntry($entry);
			} else if ($entry instanceof ValidationRequestEntry) {
				$this->entries[] = $entry;
			} else {
				throw new InvalidArgumentException('Invalid input entry: it must be either a string representing the email address or a ValidationRequestEntry instance.');
			}
		}
	}
}
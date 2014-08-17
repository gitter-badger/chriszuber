<?php
	class CSV {
		private $data, $fields, $row, $empty_row, $csv;
		public $delimiter, $enclosure, $print_headers;

		public function __construct() {
			/**
			 * Sets up default values for class
			 * 
			 * $data needs to be a multi-dimenstional associative array
			 * 
			 * $fields is a list of all columns to be included in the CSV,
			 * in the order that they should appear.
			 * 
			 * $row is the current row (integer) to be working on. Incremented
			 * by next_row() method.
			 * 
			 * $empty_row as an associative array with its keys defined by $fields,
			 * but all of its values null
			 * 
			 * $delimeter and $enclosure are charcters specifially for CSV that
			 * define separation of fields (',' between fields and '"' where values contain ',' or spaces)
			 * 
			 * @param mixed arguments (will take arguments as an array or comma separated list, either results in an array)
			 * @example $csv = new CSV($fields[] | 'field1'[, ...])
			 */

			$this->data = [];
			$this->fields = flatten(func_get_args());
			$this->row = 0;
			$this->delimiter = ',';
			$this->enclosure = '"';
			$this->print_headers = false;
			$this->csv = null;
			$this->empty_row = array_combine($this->fields, array_pad([], count($this->fields), null));
			$this->data[0] = $this->empty_row;
		}

		public function __set($field, $value) {
			/**
			 * Magic setter for the class.
			 * 
			 * Calls the private set() method too add a value to a field
			 * @param string $field
			 * @param string $value
			 * @return void
			 * @example $csv->$field = $value
			 */
			
			$this->set($field, $value);
		}
		
		public function __get($field) {
			/**
			 * Magic getter method for the class
			 * Allows for fields to be appended to rather than having to
			 * be built ahead of time.
			 * 
			 * @param string $field
			 * @return string
			 * @example $csv->field .= ' and on and on...'
			 */
			
			if(in_array($field, $this->fields)) {
				return $this->data[$this->row][$field];
			}
			else {
				return '';
			}
		}

		public function __call($field, $arguments) {
			/**
			 * Chaninable magic method, in this case only to set values
			 * 
			 * Also calls the private set() method too add a value to a field
			 * 
			 * @param string $field
			 * @param string $value
			 * @return self
			 * @example $csv->$field1($value1)->$field2($value2)...
			 */
			
			$this->set($field, $arguments[0]);

			return $this;
		}

		public function next_row() {
			/**
			 * Method to move to the next row of $data array.
			 * Increments $row, which is used in set() method
			 * when settings data ($data[$row]).
			 * 
			 * Also sets the data for that row to an empty
			 * array pre-set with the keys defined by $fields
			 * 
			 * @param void
			 * @return void
			 * @example $csv->next_row();
			 */
			
			$this->row++;
			$this->data[$this->row] = $this->empty_row;
			
			return $this;
		}
		
		public function out($newline = null) {
			/*
			 * Returns all $data as a CSV formatted string
			 * 
			 * Uses private build_CSV() method to convert $data
			 * array into CSV
			 * 
			 * @param string $newline (optional newline conversion)
			 * @return string (CSV formatted string from $data)
			 */
			
			$this->build_CSV($newline);
			return $this->csv;
		}
		
		public function save($fname = 'out', $newline = null) {
			/*
			 * Saves all $data as a CSV file
			 * 
			 * Uses private build_CSV() method to convert $data
			 * array into CSV
			 * 
			 * @param string $fname (name without extension which is automatically added)
			 * @param string $newline (optional newline conversion)
			 * @return boolean (whether or not save was successful)
			 */
			
			$this->build_CSV($newline);
			return file_put_contents(BASE . "/{$fname}.csv", $this->csv);
		}
		
		private function set($field, $value) {
			/**
			 * Private method for setting fields for the current $row
			 * 
			 * Checks if $field is in the array of available $fields
			 * and that both arguments are strings.
			 * 
			 * If these conditions are true, it sets $data[$row][$field] to $value
			 * and returns true.
			 * 
			 * Otherwise returns false without setting any data
			 * 
			 * @param string $field (name of field to set for current row)
			 * @param string $value (value to set it to)
			 * @return boolean (whether or not $field is available)
			 * @example $this->set()
			 */
			
			if(is_string($field) and in_array($field, $this->fields)) {
				$this->data[$this->row][$field] = (string)$value;
				return true;
			}
			
			return false;
		}
		
		private function build_CSV($newline = null) {
			/**
			 * Private method for converting a multi-dimensional associate array into CSV string
			 * 
			 * Opens php://temp with read/write permissions, then
			 * loops through $data, appending each row to CSV formatted string
			 * using fputscsv(). If print_headers is true, the first row will be all $fields.
			 * 
			 * If $newline is passed, it will convert PHP_EOl to $newline (Must use double quotes)
			 * 
			 * Once all $data has been looped through, it sets $csv to the value of the CSV string
			 * 
			 * @param string $newline
			 * @return void
			 * @example $this->build_CSV()
			 */
			
			/*$mk_csv = function(&$str, $key) {
				$str = $this->enclosure . str_replace($this->enclosure, $this->enclosure . $this->enclosure, $str) . $this->enclosure;
			}
			array_walk_recursive($this->data, $mk_csv);
			$this->csv = join($this->delimiter, $this->data);*/
			
			if(is_null($this->csv)) {
				 // Open a memory "file" for read/write...
				$fp = fopen('php://temp', 'r+');
				// ... write the $input array to the "file" using fputcsv()...
				if($this->print_headers) {
					fputcsv($fp, $this->fields, $this->delimiter, $this->enclosure);
					//fputs($fp, PHP_EOL);
				}
				foreach($this->data as $row) {
					fputcsv($fp, $row, $this->delimiter, $this->enclosure);
				}
				// ... rewind the "file" so we can read what we just wrote...
				rewind($fp);
				// ... read the entire line into a variable...
				$this->csv = rtrim(stream_get_contents($fp));
				fclose($fp);
				if(is_string($newline)) {
					$this->csv = str_replace(PHP_EOL, $newline, $this->csv);
				}
			}
		}
	}
?>
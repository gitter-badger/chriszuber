<?php
	class _pdo {
		/**
		 * @author Chris Zuber <shgysk8zer0@gmail.com>
		 * @copyright 2014, Chris Zuber
		 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
		 * @package core_shared
		 * @version 2014-04-19
		 */

		protected $pdo, $prepared, $data = array();
		private $query;
		protected static $instance = null;

		public static function load() {
			/**
			 * Static load function avoids creating multiple instances/connections
			 * It checks if an instance has been created and returns that or a new instance
			 *
			 * @params void
			 * @return pdo_object/class
			 * @example $pdo = _pdo::load
			 */

			if(is_null(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			/**
			 * Gets database connection info from /connect.ini (using ini::load)
			 * Uses that data to create a new PHP Data Object
			 *
			 * @param void
			 * @return void
			 * @example $pdo = new _pdo()
			 */

			$connect = ini::load('connect');
			
			try{
				if(!(isset($connect->user) and isset($connect->password))) throw new Exception('Missing credentials to connect to database');
				$connect_string = (isset($connect->type)) ? "{$connect->type}:" : 'mysql:';
				$connect_string .= (isset($connect->database)) ?  "dbname={$connect->database}" : "dbname={$connect->user}";
				if(isset($connect->server)) $connect_string .= ";host={$connect->server}";
				if(isset($connect->port)) $connect_string .= ";port={connect->port}";
				$this->pdo = new PDO($connect_string, $connect->user, $connect->password);
			}
			catch(Exception $e) {
				$this->log(__METHOD__, __LINE__, $connect_string);
				exit('Failed to connect to database.');
			}
		}

		public function log($method, $line, $message = '') {
			file_put_contents(BASE . '/' . __CLASS__ . '.log', "Error in $method in line $line: $message\n", FILE_APPEND | LOCK_EX);
		}

		public function __set($key, $value) {
			/**
			 * Setter method for the class.
			 *
			 * @param string $key, mixed $value
			 * @return void
			 * @example "$pdo->key = $value"
			 */

			$key = preg_replace('/_/', '-', preg_quote($key, '/'));
			$this->data[$key] = $value;
		}

		public function __get($key) {
			/**
			 * The getter method for the class.
			 *
			 * @param string $key
			 * @return mixed
			 * @example "$pdo->key" Returns $value
			 */

			$key = preg_replace('/_/', '-', preg_quote($key, '/'));
			if(array_key_exists($key, $this->data)) {
				return $this->data[$key];
			}
			return false;
		}

		public function __isset($key) {
			/**
			 * @param string $key
			 * @return boolean
			 * @example "isset({$pdo->key})"
			 */

			return array_key_exists(preg_replace('/_/', '-', $key), $this->data);
		}

		public function __unset($key) {
			/**
			 * Removes an index from the array.
			 *
			 * @param string $key
			 * @return void
			 * @example "unset($pdo->key)"
			 */

			unset($this->data[preg_replace('/_/', '-', $key)]);
		}

		public function __call($name, $arguments) {
			/**
			 * Chained magic getter and setter
			 * @param string $name, array $arguments
			 * @example "$pdo->[getName|setName]($value)"
			 */

			$name = strtolower($name);
			$act = substr($name, 0, 3);
			$key = preg_replace('/_/', '-', substr($name, 3));
			switch($act) {
				case 'get':
					if(array_key_exists($key, $this->data)) {
						return $this->data[$key];
					}
					else{
						return false;
					}
					break;
				case 'set':
					$this->data[$key] = $arguments[0];
					return $this;
					break;
				default:
					throw new Exeption("Unknown method: {$name} in " . __CLASS__ .'->' . __METHOD__);
			}
		}

		public function keys() {
			/**
			 * Show all keys for entries in $this->data array
			 *
			 * @param void
			 * @return array
			 */

			return array_keys($this->data);
		}

		public function prepare($query) {
			/**
			 * Argument $query is a SQL query in prepared statement format
			 * "SELECT FROM `$table` WHERE `column` = ':$values'"
			 * Note the use of the colon. These are what we are going to be
			 * binding values to a little later
			 *
			 * Returns $this for chaining. Most further functions will do the same where useful
			 *
			 * @param string $query
			 * @return self
			*/

			$this->prepared = $this->pdo->prepare($query);
			return $this;
		}

		public function bind($array) {
			/**
			 * Binds values to prepared statements
			 *
			 * @param array $array
			 * @return self
			 */
			foreach($array as $paramater => $value) {
				$this->prepared->bindValue(':' . $paramater, $value);
			}
			return $this;
		}

		public function execute() {
			/**
			 * Executes prepared statements. Does not return results
			 *
			 * @param void
			 * @return self
			 */

			if($this->prepared->execute()) {
				return $this;
			}
			return false;
		}

		public function get_results($n = null) {
			/**
			 * Gets results of prepared statement. $n can be passed to retreive a specific row
			 *
			 * @param [int $n]
			 * @return mixed
			 */

			$arr = $this->prepared->fetchAll(PDO::FETCH_CLASS);
			$results = array();
			foreach($arr as $data) {							//Convert from an associative array to a stdClass object
				$row = new stdClass();
				foreach($data as $key => $value) {
					$row->$key = trim($value);
				}
				array_push($results, $row);
			}
			//If $n is set, return $results[$n] (row $n of results) Else return all
			if(!count($results)) return false;
			if(is_null($n)) return $results;
			else return $results[$n];
		}

		public function close() {
			/**
			 * Need PDO method to close database connection
			 *
			 * @param void
			 * @return void
			 * @todo Make it actually close the connection
			 */

			unset($this);
		}

		public function escape($query) {
			/**
			 * For lack of a pdo escape, use quote, trimming off the quotations
			 *
			 * @param string $query
			 * @return string
			 */

			$escaped = unquote(
				$this->pdo->quote(
					trim($query)
				)
			);
			return $escaped;
		}

		public function query($query) {
			/**
			 * Get the results of a SQL query
			 *
			 * @param string $query
			 * @return
			 */

			return $this->pdo->query($query);
		}

		public function restore($fname) {
			/**
			 * Restores a MySQL database from file $fname
			 *
			 * @param string $fname
			 * @return self
			 */

			$sql = file_get_contents(BASE ."/{$fname}.sql");
			$this->pdo->query($sql);
			return $this;
		}
		public function fetch_array($query, $n = null) {
			/**
			 * Return the results of a query as an associative array
			 *
			 * @param string $query
			 * @return array
			 */

			//$results = $this->query($query);
			$data = $this->query($query)->fetchAll(PDO::FETCH_CLASS);
			if(is_array($data)){
				return (is_null($n)) ? $data : $data[$n];
			}
			return [];
		}

		public function get_table($table, $these = '*') {
			/**
			 * @param string $table[, string $these]
			 * @return array
			 */

			if($these !== '*') $these ="`{$these}`";
			return $this->fetch_array("SELECT {$these} FROM {$this->escape($table)}");
		}

		public function array_insert($table, $content) {
			/**
			 *
			 * @param string $table, array $content
			 * @return self
			 * @example "$pdo->array_insert($table, array('var1' => 'value1', 'var2' => 'value2'))"
			 */

			foreach($content as &$value) $value = $this->pdo->quote($value);
			$query = "INSERT into `{$this->escape($table)}` (`". join('`,`', array_keys($content)) . "`) VALUES(" . join(',', $content) . ")";
			$resp = $this->pdo->query($query);
			return $resp;
		}

		public function sql_table($table_name) {
			/**
			 * Prints out a SQL table in HTML formnatting. Used for updating via Ajax
			 *
			 * @param string $table_name
			 * @return string (html table)
			 */

			$table_data = $this->get_table($table_name);
			if(!is_array($table_data)) return false;
			(count($table_data)) ? $cols = array_keys(get_object_vars($table_data[0])) : $cols = $this->table_headers($table_name);
			$table = "<table border=\"1\" data-nonce=\"{$_SESSION['nonce']}\" data-sql-table=\"{$table_name}\">";
			$thead = '<thead><tr>';
			foreach($cols as $col) {
				if($col !== 'id') {
					$thead .= "<th>{$col}</th>";
				}
			}
			$thead .= "</tr></thead>";
			$tbody = "<tbody>";
			if(count($table_data)) {
				foreach($table_data as $tr) {
					$tbody .= "<tr data-sql-id=\"{$tr->id}\">";
					foreach($tr as $key => $td) {
						if($key !== 'id') {
							$tbody .= "<td><input name={$key} type=\"text\" value=\"{$td}\" class=\"sql\">";
						}
					}
				}
			}
			$tbody .="</tbody>";
			$table .= $thead . $tbody .= "</table>";
			return $table;
		}

		public function update($table, $name, $value, $where) {
			/**
			 * Updates a table according to these arguments
			 *
			 * @param string $table, string $name, string $value, string $where
			 * @return
			 */

			return $this->query("UPDATE `{$table}` SET `{$name}` = '{$value}' WHERE {$where}");
		}

		public function show_tables() {
			/**
			 * Returns a 0 indexed array of tables in database
			 *
			 * @param void
			 * @return array
			 */

			$query = "SHOW TABLES";
			$results = $this->pdo->query($query);
			$tables = $results->fetchAll(PDO::FETCH_COLUMN, 0);
			return $tables;
		}

		public function table_headers($table) {
			/**
			 * Returns a 0 indexed array of column headers for $table
			 *
			 * @param string $table
			 * @return array
			 */

			$query = "DESCRIBE {$this->escape($table)}";
			$results = $this->pdo->query($query);
			$headers = $results->fetchAll(PDO::FETCH_COLUMN, 0);
			return $headers;
		}

		public function describe($table) {
			/**
			 * Describe $table, including:
			 * Field {name}
			 * Type {varchar|int... & (length)}
			 * Null (boolean)
			 * Default {value}
			 * Extra {auto_increment, etc}
			 * 
			 * @param string $table
			 * @return array
			 */
			return $this->pdo->query("DESCRIBE {$this->escape($table)}")->fetchAll(PDO::FETCH_CLASS);
		}
		
		public function value_properties($query) {
			/**
			 * Returns the results of a SQL query as a stdClass object
			 *
			 * @param string $query
			 * @return array
			 */

			$array = array();
			$results = $this->fetch_array($query);
			foreach($results as $result) {
				$data = new stdClass();
				foreach($results as $key => $value) {
					$key = trim($key);
					$value = trim($value);
					$data->$key = $value;
				}
				array_push($array, $data);
			}
			return $array;
		}

		public function name_value($table = null) {
			/**
			 * For simple Name/Value tables. Gets all name/value pairs. Returns stdClass object
			 *
			 * @param [string $table]
			 * @return obj
			 */

			$data = $this->fetch_array("SELECT `name`, `value` FROM `{$this->escape($table)}`");
			$values = new stdClass();
			foreach($data as $row) {
				$name = trim($row->name);
				$value = trim($row->value);
				$values->$name = $value;
			}
			return $values;
		}

		public function reset_table($table) {
			/**
			 * Removes all entries in a table and resets AUTO_INCREMENT to 1
			 *
			 * @param string $table
			 * @return void
			 */

			$table = $this->escape($table);
			$this->query("DELETE FROM `{$table}`");
			$this->query("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
			return $this;
		}
	}
?>

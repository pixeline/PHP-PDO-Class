<?php
/**
 * Simple PHP PDO Class
 * @author Miks Zvirbulis (twitter.com/MiksZvirbulis),
 * @author Alexandre Plennevaux (twitter.com/pixeline)
 * @version 2.0
 * 2.0 - insert returns last insert id. Force UTF8. Proper phpDoc syntax.
 * 1.1 - Added a constructor which allows multiple databases to be called on different variables.
 * 1.0 - First version launched. Allows access to one database and a few regular functions have been created.
 */
class db
{
	# Database host address, defined in construction.
	protected $host;
	# Username for authentication, defined in construction.
	protected $username;
	# Password for authentication, defined in construction.
	protected $password;
	# Database name, defined in construction.
	protected $database;

	# Connection variable. DO NOT CHANGE!
	protected $connection;

	# @bool default for this is to be left to FALSE, please. This determines the connection state.
	public $connected = false;

	# @bool this controls if the errors are displayed. By default, this is set to true.
	private $errors = true;
	public $queries;

	function __construct($db_host, $db_username, $db_password, $db_database)
	{
		global $c;
		$this->host = $db_host;
		$this->username = $db_username;
		$this->password = $db_password;
		$this->database = $db_database;
		$this->connected = true;
		$this->establishConnection();
	}

	/**
	 * Initializes the database connection.
	 * @return bool
	 */
	private function establishConnection()
	{
		try {
			$this->connection = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->database, $this->username, $this->password);
			$this->connection->exec("set names utf8");
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			return true;
		}
		catch (PDOException $e) {
			$this->connected = false;
			if ($this->errors === true) {
				return $this->error($e->getMessage());
			}
			return false;
		}
	}


	function __destruct()
	{
		$this->connected = false;
		$this->connection = null;
	}

	/**
	 * Echoes an error message.
	 * @param mixed $error
	 * @return void
	 */
	public function error($error)
	{
		echo $error;
	}

	/**
	 * Calling a query which will return only ONE row. Usage: (query, array with data). Returns an array.
	 * @param mixed $query
	 * @param mixed $parameters
	 * @return mixed
	 */
	public function fetch($query, $parameters = array())
	{

		if ($this->connected === true) {
			try {
				$query = $this->connection->prepare($query);
				$query->execute($parameters);
				$this->queries[] = $query;

				return $query->fetch();
			}
			catch (PDOException $e) {
				if ($this->errors === true) {
					return $this->error($e->getMessage());
				}
				else {
					return false;
				}
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Calling a query which will return multiple rows. Usage: (query, array with data). Returns an array.
	 * @param mixed $query
	 * @param mixed $parameters
	 * @return array|bool
	 */
	public function fetchAll($query, $parameters = array())
	{

		if ($this->connected === true) {
			try {
				$query = $this->connection->prepare($query);
				$query->execute($parameters);
				$this->queries[] = $query;
				return $query->fetchAll();
			}
			catch (PDOException $e) {
				if ($this->errors === true) {
					return $this->error($e->getMessage());
				}
				else {
					return false;
				}
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Returns the total count of rows. Usage: (query, array with data). Returns an integer.
	 * @param mixed $query
	 * @param mixed $parameters
	 * @return bool|int
	 */
	public function count($query, $parameters = array())
	{
		if ($this->connected === true) {
			try {
				$query = $this->connection->prepare($query);
				$this->queries[] = $query;
				$query->execute($parameters);
				return $query->rowCount();
			}
			catch (PDOException $e) {
				if ($this->errors === true) {
					return $this->error($e->getMessage());
				}
				else {
					return false;
				}
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Inserts a row into a table. This can also create a table.
	 * @param mixed $query
	 * @param mixed $parameters
	 * @return bool
	 */
	public function insert($query, $parameters = array())
	{
		if ($this->connected === true) {
			try {
				$query = $this->connection->prepare($query);
				$this->queries[] = $query;
				$query->execute($parameters);
				$this->connection->lastInsertId();
			}
			catch (PDOException $e) {
				if ($this->errors === true) {
					return $this->error($e->getMessage());
				}
				return false;
			}
		}
		return false;
	}

	public function update($query, $parameters = array())
	{
		if ($this->connected === true) {
			return $this->insert($query, $parameters);
		}
		return false;
	}

	public function delete($query, $parameters = array())
	{
		if ($this->connected === true) {
			return $this->insert($query, $parameters);
		}
		else {
			return false;
		}
	}

	public function tableExists($table)
	{
		if ($this->connected === true) {
			try {
				$query = $this->count("SHOW TABLES LIKE '$table'");
				return ($query > 0) ? true : false;
			}
			catch (PDOException $e) {
				if ($this->errors === true) {
					return $this->error($e->getMessage());
				}
				return false;
			}
		}
		return false;
	}
}

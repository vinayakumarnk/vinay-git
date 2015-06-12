<?php

/**
* Handling Database connection
*/
class DbConnect{
	
	private $conn;
	
	function __construct(){	
	}
	
	/**
	* Establishing a database connection
	* @return Database Connection handler
	*/
	function connect(){
	
		require_once dirname(__FILE__).'/Config.php';
		
		//connecting mysql database
		$this->conn	=	new MySQLi(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
                
                //check for database connection error
                if(mysqli_connect_errno()){
                    echo 'Failed to connect to MySQL '.mysqli_connect_error();
                }
                
                //returning connection resource
                return $this->conn;
	}	



}

?>
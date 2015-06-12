<?php
/**
 * Class to handle all DB opeations
 * This class will have all CRUD methods
 */

class DbHandler{
    
    private $conn;
    
    function __construct() {
        require_once dirname(__FILE__).'/DbConnect.php';
        //opening db connection
        $db  =  new DbConnect();
        $this->conn = $db->connect();
    }
    
    
    /**
     * creating new user
     */
    
    public function createUser($name, $email, $password, $profile_pic){
        $response  =  array();
        if(!$this->isUserExists($email)){
            //md5 password encryption
            $password  = md5($password);
            
            $stmt   = $this->conn->prepare("INSERT INTO `users`(`name`, `email`, `password`, `profile_pic`) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password, $profile_pic);
            
            $result  =  $stmt->execute();
            $stmt->close();
            
            if($result){
                return USER_CREATED_SUCCESSFULLY;
            }
            else {
                return USER_CREATE_FAILED;
            }
        }
        else{
            return USER_ALREADY_EXISTED;
        }
        
        return $response;
    }
    
    
    /**
     * check login
     */
       public function checkLogin($email, $password) {
        // fetching user by email
  $password  = md5($password);         
        $stmt = $this->conn->prepare("SELECT name FROM users WHERE email = ? AND password = ?");


//print_r($email);print_r($password);
//$name  = 'mathan';
        $stmt->bind_param("ss", $email, $password);
        

        $stmt->execute();
 
        $stmt->bind_result($name);

        $stmt->store_result();
//sprint_r($stmt->num_rows); exit();
      //  $stmt->bind_result($password_hash);

      //  $stmt->store_result();

            if ($stmt->num_rows > 0) {
                           
                $stmt->close();

                return TRUE;
            } 
            else {
                $stmt->close();
                // user not existed with the email
                return FALSE;
        }
    }

    
    /**
     * Get User details by email id
     * @param type $email
     * @return type
     */
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT name, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($name, $email);
            $stmt->fetch();
            $user = array();
            $user["name"] = $name;
            $user["email"] = $email;            
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    
        /**
     * Checking for email already in DB or not
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }
}
?>
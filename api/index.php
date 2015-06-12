<?php
require_once '../include/DbHandler.php';

require '../libs/Slim/Slim.php';
\Slim\Slim::registerAutoloader();


$app = new \Slim\Slim();


//register
$app->post('/register', function() use ($app){
    
//    print_r($_FILES);print_r($_POST);exit();
    
                //check for required params present or not
                verifyRequiredParams(array('name','email','password'));
    
                $response   =   array();
                
                $name      = $app->request->post('name');
                $email     = $app->request->post('email');
                $password  = $app->request->post('password');
                
                
                //validating emailaddress
                validateEmail($email);
                $filename  = '';
//                print_r($_FILES);exit();
                if(isset($_FILES['profile']['name']) && $_FILES['profile']['name']!=''){
                     $image 			=		$_FILES["profile"];
                     
                     $filename       =  stripslashes($image['name']);
                     
                     $imagename      =  $image['name'];
                     $newimagename   =  preg_replace('/[^a-zA-Z0-9._]/s', '', $imagename);
                     $img_path       =  rand(111, 999).'_'.$newimagename;
                     
                     $filename       =  $img_path;
                     
                     $filename_up    =  "../pics/".$filename;
                     
                     $out  =   move_uploaded_file($image['tmp_name'],$filename_up);                                          
                }
                
                $db   =  new DbHandler();
                
                $res    =  $db->createUser($name, $email, $password, $filename);
                
                if($res == 'USER_CREATED_SUCCESSFULLY'){
                    $response['error']    =   FALSE;
                    $response['message']  =   'You are successfully registered';
                }
                elseif ($res  ==  'USER_CRATE_FAILED') {
                    $response['error']    =   TRUE;
                    $response['message']  =   'Oops! error occured while registering';               
                }
                elseif ($res    ==  'USER_ALREADY_EXISTED') {
                    $response['error']    =   TRUE;
                    $response['message']  =   'Sorry! this email already existed';                
                }
                //echo json response
                echoRespnse(201, $response);                                
            });

//login
$app->post('/login', function() use ($app){
	
            // check for required params
            verifyRequiredParams(array('email', 'password'));

            // reading post params
            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandler();
            // check for correct email and password
            if ($db->checkLogin($email, $password)) {
                // get the user by email
                $user = $db->getUserByEmail($email);
           //     print_r($user);exit();
                if ($user != NULL) {
                    $response["error"] = false;
                    $response['name'] = $user['name'];
                    $response['email'] = $user['email'];                   
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // user credentials are wrong
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';
            }

            echoRespnse(200, $response);
        
	
	
	});        
        
/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
	
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

        
$app->run();

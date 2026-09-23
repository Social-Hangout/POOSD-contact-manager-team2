<?php

session_start();


function respond($status,$data,$message){
  
	$payload = array('status' => $status);

	if ($data !== null)
	{
		$payload['data'] = $data;
	}

	if ($message !== '')
	{
		$payload['message'] = $message;
	}

	echo json_encode($payload);
	exit;
}

$input=json_decode(file_get_contents('php://input'), true);



$log_email=isset($input['email']) ? trim($input['email']): '';
if ($log_email===''){ respond('error',null,'Email is required');}

$log_pass=isset($input['password']) ? trim($input['password']): '';
if ($log_pass===''){ respond('error',null,'Password is required');}




require_once __DIR__ . '/../config.php';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if($conn->connect_error){
    respond('error',null,'Database connection failed');
}

$stmt=$conn->prepare('SELECT id, password_hash  FROM users WHERE email=?');
$stmt->bind_param('s', $log_email);
$stmt->execute();
$result=$stmt->get_result();

$row=$result->fetch_assoc();

if(!$row){
    respond('error',null,'Email or password is incorrect');
   
}
$user_id=$row['id'];
$user_pass=$row['password_hash'];

if(!password_verify($log_pass,$user_pass)){
    respond('error',null,'Email or password is incorrect');


}

$_SESSION['user_id']=$user_id;

$payload = [
    "status" => "success",
    "user_id"=>$user_id
    
    ];



echo json_encode($payload);
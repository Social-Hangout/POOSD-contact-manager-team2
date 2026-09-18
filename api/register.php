<?php


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



$reg_email=isset($input['email']) ? trim($input['email']): '';
if ($reg_email===''){ respond('error',null,'Email is required');}

$reg_pass=isset($input['password']) ? trim($input['password']): '';
if ($reg_pass===''){ respond('error',null,'Password is required');}

$reg_user_name=isset($input['username']) ? trim($input['username']): '';
if ($reg_user_name===''){ respond('error',null,'Username is required');}




$password_hash=password_hash($reg_pass,PASSWORD_DEFAULT);

require_once __DIR__ . '/../config.php';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if($conn->connect_error){
    respond('error',null,'Database connection failed');
}

$stmt=$conn->prepare('SELECT id FROM users WHERE email=?');
$stmt->bind_param('s', $reg_email);
$stmt->execute();
$result=$stmt->get_result();

if($result->num_rows>0){
    respond('error',null,"Email already exist");
}

$stm=$conn->prepare('INSERT INTO users (email,username,password_hash) VALUES(?,?,?)');
$stm->bind_param('sss',$reg_email,$reg_user_name, $password_hash);
$stm->execute();

$user_id=$conn->insert_id;




$payload = [
    "status" => "success",
    "user_id"=>$user_id
    
    ];



echo json_encode($payload);
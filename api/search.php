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

$search_name=isset($_GET['q']) ? trim($_GET['q']): '';
if ($search_name===''){ respond('error',null,'Enter a name to search');}

$user_id=isset($_SESSION['user_id']) ? trim($_SESSION['user_id']): '';
if ($user_id===''){ respond('unathorized',null,'unauthenticated user');}

$pattern='%' . $search_name . '%';



require_once __DIR__ . '/../config.php';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if($conn->connect_error){
    respond('error',null,'Database connection failed');
}

$stmt=$conn->prepare("SELECT first_name, last_name, email, phone  FROM contacts WHERE user_id=? AND (first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name,' ',last_name) LIKE ?)");
$stmt->bind_param('isss', $user_id, $pattern ,$pattern,$pattern);
$stmt->execute();
$result=$stmt->get_result();





$contacts= array();
while($row=$result->fetch_assoc()){
    $contacts[]=$row;
    


}





$payload = [
    "status" => "success",
    "contacts"=>$contacts,
    
    ];



echo json_encode($payload);
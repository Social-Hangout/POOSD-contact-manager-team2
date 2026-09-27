<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/auth.php';

function respond($status, $data, $message)
{
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

$input = json_decode(file_get_contents('php://input'), true);

$log_email = isset($input['email']) ? trim($input['email']) : '';
if ($log_email === '')
{
	respond('error', null, 'Email is required');
}

$log_pass = isset($input['password']) ? trim($input['password']) : '';
if ($log_pass === '')
{
	respond('error', null, 'Password is required');
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if ($conn->connect_error)
{
	respond('error', null, 'Database connection failed');
}

$stmt = $conn->prepare('SELECT id, password_hash FROM users WHERE email=?');
$stmt->bind_param('s', $log_email);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();

if (!$row)
{
	respond('error', null, 'Email or password is incorrect');
}

$user_id = $row['id'];
$user_pass = $row['password_hash'];

if (!password_verify($log_pass, $user_pass))
{
	respond('error', null, 'Email or password is incorrect');
}

$token = createApiToken();
$update = $conn->prepare('UPDATE users SET api_token = ? WHERE id = ?');
if (!$update)
{
	$conn->close();
	respond('error', null, 'Failed to issue API token');
}

$update->bind_param('si', $token, $user_id);
if (!$update->execute())
{
	$update->close();
	$conn->close();
	respond('error', null, 'Failed to issue API token');
}
$update->close();

$_SESSION['user_id'] = $user_id;

$payload = array(
	'status' => 'success',
	'user_id' => (int)$user_id,
	'token' => $token
);

echo json_encode($payload);
$conn->close();
?>

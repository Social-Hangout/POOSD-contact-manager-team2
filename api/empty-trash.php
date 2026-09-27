<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config.php';

function respond($status, $message = '')
{
	$payload = array('status' => $status);

	if ($message !== '')
	{
		$payload['message'] = $message;
	}

	echo json_encode($payload);
	exit;
}

if (!isset($_SESSION['user_id']))
{
	respond('error', 'Not logged in');
}

$userId = (int)$_SESSION['user_id'];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_error)
{
	respond('error', 'Database connection failed');
}

$stmt = $conn->prepare(
	'DELETE FROM contacts WHERE user_id = ? AND is_deleted = 1'
);

if (!$stmt)
{
	$conn->close();
	respond('error', 'Failed to prepare statement');
}

$stmt->bind_param('i', $userId);

if (!$stmt->execute())
{
	$stmt->close();
	$conn->close();
	respond('error', 'Failed to empty trash');
}

$stmt->close();
$conn->close();

respond('success');
?>

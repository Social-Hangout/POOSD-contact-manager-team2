<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config.php';

function respond($status, $contactId = null, $message = '')
{
	$payload = array('status' => $status);

	if ($contactId !== null)
	{
		$payload['contact_id'] = (int)$contactId;
	}

	if ($message !== '')
	{
		$payload['message'] = $message;
	}

	echo json_encode($payload);
	exit;
}

if (!isset($_SESSION['user_id']))
{
	respond('error', null, 'Not logged in');
}

$inData = json_decode(file_get_contents('php://input'), true);

if (!is_array($inData))
{
	respond('error', null, 'Invalid JSON');
}

$name  = isset($inData['name'])  ? trim($inData['name'])  : '';
$phone = isset($inData['phone']) ? trim($inData['phone']) : '';
$email = isset($inData['email']) ? trim($inData['email']) : '';
$notes = isset($inData['notes']) ? trim($inData['notes']) : '';

if ($name === '')
{
	respond('error', null, 'Name is required');
}

$userId = (int)$_SESSION['user_id'];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error)
{
	respond('error', null, 'Database connection failed');
}

$stmt = $conn->prepare(
	'INSERT INTO contacts (user_id, name, phone, email, notes) VALUES (?, ?, ?, ?, ?)'
);

if (!$stmt)
{
	$conn->close();
	respond('error', null, 'Failed to prepare statement');
}

$stmt->bind_param('issss', $userId, $name, $phone, $email, $notes);

if (!$stmt->execute())
{
	$stmt->close();
	$conn->close();
	respond('error', null, 'Failed to add contact');
}

$contactId = $conn->insert_id;

$stmt->close();
$conn->close();

respond('success', $contactId);
?>

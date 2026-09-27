<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/auth.php';

function respond($status, $contactId = null, $message = '')
{
	$payload = array('status' => $status);

	if ($contactId !== null)
	{
		$payload['id'] = (int)$contactId;
	}

	if ($message !== '')
	{
		$payload['message'] = $message;
	}

	echo json_encode($payload);
	exit;
}

$inData = json_decode(file_get_contents('php://input'), true);

if (!is_array($inData))
{
	respond('error', null, 'Invalid JSON');
}

$firstName = isset($inData['first_name']) ? trim($inData['first_name']) : '';
$lastName  = isset($inData['last_name'])  ? trim($inData['last_name'])  : '';
$phone     = isset($inData['phone'])      ? trim($inData['phone'])      : '';
$email     = isset($inData['email'])      ? trim($inData['email'])      : '';

if ($firstName === '' || $lastName === '')
{
	respond('error', null, 'First name and last name are required');
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_error)
{
	respond('error', null, 'Database connection failed');
}

$userId = requireUserIdFromToken($conn, function ($message) {
	respond('error', null, $message);
}, $inData);

$stmt = $conn->prepare(
	'INSERT INTO contacts (user_id, first_name, last_name, email, phone) VALUES (?, ?, ?, ?, ?)'
);

if (!$stmt)
{
	$conn->close();
	respond('error', null, 'Failed to prepare statement');
}

$stmt->bind_param('issss', $userId, $firstName, $lastName, $email, $phone);

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

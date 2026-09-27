<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/auth.php';

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

$inData = json_decode(file_get_contents('php://input'), true);

if (!is_array($inData))
{
	respond('error', 'Invalid JSON');
}

$rawId = null;
if (isset($inData['id']) && is_numeric($inData['id']))
{
	$rawId = $inData['id'];
}
elseif (isset($inData['contact_id']) && is_numeric($inData['contact_id']))
{
	$rawId = $inData['contact_id'];
}

if ($rawId === null)
{
	respond('error', 'id is required');
}

$firstName = isset($inData['first_name']) ? trim($inData['first_name']) : '';
$lastName  = isset($inData['last_name'])  ? trim($inData['last_name'])  : '';
$phone     = isset($inData['phone'])      ? trim($inData['phone'])      : '';
$email     = isset($inData['email'])      ? trim($inData['email'])      : '';

if ($firstName === '' || $lastName === '')
{
	respond('error', 'First name and last name are required');
}

$contactId = (int)$rawId;

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_error)
{
	respond('error', 'Database connection failed');
}

$userId = requireUserIdFromToken($conn, function ($message) {
	respond('error', $message);
}, $inData);

$stmt = $conn->prepare(
	'UPDATE contacts SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ? AND user_id = ?'
);

if (!$stmt)
{
	$conn->close();
	respond('error', 'Failed to prepare statement');
}

$stmt->bind_param('ssssii', $firstName, $lastName, $email, $phone, $contactId, $userId);

if (!$stmt->execute())
{
	$stmt->close();
	$conn->close();
	respond('error', 'Failed to edit contact');
}

if ($stmt->affected_rows === 0)
{
	$stmt->close();

	$check = $conn->prepare('SELECT id FROM contacts WHERE id = ? AND user_id = ?');
	if (!$check)
	{
		$conn->close();
		respond('error', 'Failed to verify contact');
	}

	$check->bind_param('ii', $contactId, $userId);
	$check->execute();
	$exists = $check->get_result()->fetch_assoc();
	$check->close();
	$conn->close();

	if (!$exists)
	{
		respond('error', 'Contact not found');
	}

	respond('success');
}

$stmt->close();
$conn->close();

respond('success');
?>

<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config.php';

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

$inData = json_decode(file_get_contents('php://input'), true);

if (!is_array($inData))
{
	respond('error', 'Invalid JSON');
}

if (!isset($inData['contact_id']) || !is_numeric($inData['contact_id']))
{
	respond('error', 'contact_id is required');
}

$name  = isset($inData['name'])  ? trim($inData['name'])  : '';
$phone = isset($inData['phone']) ? trim($inData['phone']) : '';
$email = isset($inData['email']) ? trim($inData['email']) : '';
$notes = isset($inData['notes']) ? trim($inData['notes']) : '';

if ($name === '')
{
	respond('error', 'Name is required');
}

$contactId = (int)$inData['contact_id'];
$userId = (int)$_SESSION['user_id'];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error)
{
	respond('error', 'Database connection failed');
}

$stmt = $conn->prepare(
	'UPDATE contacts SET name = ?, phone = ?, email = ?, notes = ? WHERE contact_id = ? AND user_id = ?'
);

if (!$stmt)
{
	$conn->close();
	respond('error', 'Failed to prepare statement');
}

$stmt->bind_param('ssssii', $name, $phone, $email, $notes, $contactId, $userId);

if (!$stmt->execute())
{
	$stmt->close();
	$conn->close();
	respond('error', 'Failed to edit contact');
}

if ($stmt->affected_rows === 0)
{
	$stmt->close();
	$conn->close();
	respond('error', 'Contact not found');
}

$stmt->close();
$conn->close();

respond('success');
?>

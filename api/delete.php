<?php
header('Content-Type: application/json');

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

$inData = json_decode(file_get_contents('php://input'), true);

if (!is_array($inData))
{
	respond('error', 'Invalid JSON');
}

if (!isset($inData['user_id']) || !is_numeric($inData['user_id']) || (int)$inData['user_id'] <= 0)
{
	respond('error', 'Missing user_id');
}

$userId = (int)$inData['user_id'];

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

$contactId = (int)$rawId;

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_error)
{
	respond('error', 'Database connection failed');
}
$stmt = $conn->prepare(
	'UPDATE contacts SET is_deleted = 1, deleted_at = NOW() WHERE id = ? AND user_id = ? AND is_deleted = 0'
);
if (!$stmt)
{
	$conn->close();
	respond('error', 'Failed to prepare statement');
}

$stmt->bind_param('ii', $contactId, $userId);

if (!$stmt->execute())
{
	$stmt->close();
	$conn->close();
	respond('error', 'Failed to delete contact');
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

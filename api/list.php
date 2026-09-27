<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/auth.php';

function respond($status, $contacts = null, $message = '')
{
	$payload = array('status' => $status);

	if ($message !== '')
	{
		$payload['message'] = $message;
	}

	if ($contacts !== null)
	{
		$payload['contacts'] = $contacts;
	}

	echo json_encode($payload);
	exit;
}

// Body is optional for list; allow empty POST
$raw = file_get_contents('php://input');
if ($raw !== '' && $raw !== false)
{
	$inData = json_decode($raw, true);
	if ($inData !== null && !is_array($inData))
	{
		respond('error', null, 'Invalid JSON');
	}
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_error)
{
	respond('error', null, 'Database connection failed');
}

$userId = requireUserIdFromToken($conn, function ($message) {
	respond('error', null, $message);
});

$stmt = $conn->prepare(
	'SELECT id, first_name, last_name, email, phone FROM contacts WHERE user_id = ? AND is_deleted = 0 ORDER BY first_name ASC, last_name ASC');

if (!$stmt)
{
	$conn->close();
	respond('error', null, 'Failed to prepare statement');
}

$stmt->bind_param('i', $userId);

if (!$stmt->execute())
{
	$stmt->close();
	$conn->close();
	respond('error', null, 'Failed to list contacts');
}

$result = $stmt->get_result();
$contacts = array();

while ($row = $result->fetch_assoc())
{
	$contacts[] = array(
		'id'         => (int)$row['id'],
		'first_name' => $row['first_name'],
		'last_name'  => $row['last_name'],
		'email'      => $row['email'],
		'phone'      => $row['phone']
	);
}

$stmt->close();
$conn->close();

respond('success', $contacts);
?>

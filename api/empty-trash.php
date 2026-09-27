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

$inData = null;
$raw = file_get_contents('php://input');
if ($raw !== '' && $raw !== false)
{
	$decoded = json_decode($raw, true);
	if (is_array($decoded))
	{
		$inData = $decoded;
	}
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_error)
{
	respond('error', 'Database connection failed');
}

$userId = requireUserIdFromToken($conn, function ($message) {
	respond('error', $message);
}, $inData);

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

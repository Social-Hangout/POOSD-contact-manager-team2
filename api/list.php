<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config.php';

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

if (!isset($_SESSION['user_id']))
{
	respond('error', null, 'Not logged in');
}

$userId = (int)$_SESSION['user_id'];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error)
{
	respond('error', null, 'Database connection failed');
}

$stmt = $conn->prepare(
	'SELECT contact_id, name, phone, email, notes FROM contacts WHERE user_id = ? ORDER BY name ASC'
);

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
		'contact_id' => (int)$row['contact_id'],
		'name'       => $row['name'],
		'phone'      => $row['phone'],
		'email'      => $row['email'],
		'notes'      => $row['notes']
	);
}

$stmt->close();
$conn->close();

respond('success', $contacts);
?>

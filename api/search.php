<?php
header('Content-Type: application/json');

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

$search_name = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($search_name === '')
{
	respond('error', null, 'Enter a name to search');
}

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id']) || (int)$_GET['user_id'] <= 0)
{
	respond('error', null, 'Missing user_id');
}

$user_id = (int)$_GET['user_id'];
$pattern = '%' . $search_name . '%';

require_once __DIR__ . '/../../config.php';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if ($conn->connect_error)
{
	respond('error', null, 'Database connection failed');
}

$stmt = $conn->prepare(
	"SELECT id, first_name, last_name, email, phone FROM contacts WHERE user_id=? AND is_deleted = 0 AND (first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name,' ',last_name) LIKE ?)"
);
$stmt->bind_param('isss', $user_id, $pattern, $pattern, $pattern);
$stmt->execute();
$result = $stmt->get_result();

$contacts = array();
while ($row = $result->fetch_assoc())
{
	$contacts[] = $row;
}

$payload = array(
	'status' => 'success',
	'contacts' => $contacts
);

echo json_encode($payload);
?>

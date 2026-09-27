<?php
/**
 * Token auth helpers for the Contacts API.
 * Clients send X-API-Key (or Authorization: Bearer <token>) from login.
 * Never trust a client-supplied user_id.
 */

function getRequestApiToken()
{
	if (!empty($_SERVER['HTTP_X_API_KEY']))
	{
		return trim($_SERVER['HTTP_X_API_KEY']);
	}

	$auth = '';
	if (!empty($_SERVER['HTTP_AUTHORIZATION']))
	{
		$auth = $_SERVER['HTTP_AUTHORIZATION'];
	}
	elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
	{
		$auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
	}

	if ($auth !== '' && preg_match('/Bearer\s+(\S+)/i', $auth, $matches))
	{
		return trim($matches[1]);
	}

	return '';
}

function lookupUserIdByToken($conn, $token)
{
	if ($token === '')
	{
		return null;
	}

	$stmt = $conn->prepare('SELECT id FROM users WHERE api_token = ? LIMIT 1');
	if (!$stmt)
	{
		return null;
	}

	$stmt->bind_param('s', $token);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result ? $result->fetch_assoc() : null;
	$stmt->close();

	if (!$row)
	{
		return null;
	}

	return (int)$row['id'];
}

/**
 * Returns authenticated user id, or writes a JSON error and exits.
 * $respond must be a callable like: function ($status, ..., $message).
 * For simple StatusResponse-style endpoints, pass a wrapper.
 */
function requireUserIdFromToken($conn, $onUnauthorized)
{
	$token = getRequestApiToken();
	if ($token === '')
	{
		$onUnauthorized('Missing API token');
	}

	$userId = lookupUserIdByToken($conn, $token);
	if ($userId === null || $userId <= 0)
	{
		$onUnauthorized('Invalid API token');
	}

	return $userId;
}

function createApiToken()
{
	return bin2hex(random_bytes(32));
}
?>

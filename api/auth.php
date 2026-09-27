<?php
/**
 * Token auth helpers for the Contacts API.
 * Prefer X-API-Key / Bearer header; also accept api_token in JSON body or ?api_token=
 * (Apache sometimes does not expose custom headers to PHP).
 * Never trust a client-supplied user_id.
 */

function getRequestApiToken($body = null)
{
	if (!empty($_SERVER['HTTP_X_API_KEY']))
	{
		return trim($_SERVER['HTTP_X_API_KEY']);
	}

	if (function_exists('getallheaders'))
	{
		foreach (getallheaders() as $name => $value)
		{
			if (strcasecmp($name, 'X-API-Key') === 0)
			{
				return trim($value);
			}
		}
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

	if (is_array($body) && !empty($body['api_token']))
	{
		return trim((string)$body['api_token']);
	}

	if (!empty($_GET['api_token']))
	{
		return trim((string)$_GET['api_token']);
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

function requireUserIdFromToken($conn, $onUnauthorized, $body = null)
{
	$token = getRequestApiToken($body);
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

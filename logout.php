<?php
/**
 * Sign out of the Vinkius Connector panel.
 *
 * @license GPLv2 or later
 */

require_once __DIR__ . '/bootstrap.php';

$_SESSION = array();
session_destroy();

header('Location: /index.php');
exit;

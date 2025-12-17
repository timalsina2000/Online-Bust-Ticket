<?php
$s_path = __DIR__ . '/sessions';
if (!file_exists($s_path)) { @mkdir($s_path, 0777, true); }
session_save_path($s_path);

session_start();
session_destroy();
header("Location: index.php");
?>

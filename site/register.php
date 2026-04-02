<?php
header('Content-Type: text/html; charset=UTF-8');
$ref = preg_replace('/[^A-Z0-9]/', '', strtoupper($_GET['ref'] ?? ''));
$url = '/' . ($ref ? '?ref=' . $ref : '');
header('Location: ' . $url, true, 302);
exit;

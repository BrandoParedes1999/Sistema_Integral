<?php
session_start();
if (!isset($_SESSION['usuario'])) { http_response_code(401); exit; }
$_SESSION['ultimo_acceso'] = time();
header('Content-Type: application/json');
echo json_encode(['ok' => true, 'ts' => time()]);

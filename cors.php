<?php
/**
 * file: cors.php
 * author: Iya
 * date: June 25, 2026
 * purpose: Handles Cross-Origin Resource Sharing (CORS) headers to allow safe API requests from localhost and the Vercel production frontend.
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>
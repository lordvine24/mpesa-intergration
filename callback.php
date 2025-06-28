<?php
// Read the incoming JSON from Safaricom
$data = file_get_contents("php://input");

// Optional: Decode it if you want to view/parse the response
$decoded = json_decode($data, true);

// Save the raw response to a log file
file_put_contents("callback_log.txt", $data . PHP_EOL, FILE_APPEND);

// Respond with success (Daraja expects a 200 OK response)
header("Content-Type: application/json");
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Callback received successfully"]);
?>

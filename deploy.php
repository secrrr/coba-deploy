<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed. Use POST."]);
    exit;
}

// Set working directory ke folder project
$projectDir = __DIR__; // folder deploy.php berada
chdir($projectDir);

$commitMessage = "Deploy via PHP script at " . date('Y-m-d H:i:s');
$commitMessageEscaped = escapeshellarg($commitMessage); // ini penting!

$currentBranch = trim(shell_exec("git rev-parse --abrev-ref HEAD"));
$commands = [
    "git add .",
    "git commit -m $commitMessageEscaped",
    "git push origin $currentBranch"
];

$output = [];
$errors = [];

foreach ($commands as $cmd) {
    exec($cmd . " 2>&1", $out, $return_var);
    $output[] = implode("\n", $out);
    if ($return_var !== 0) {
        $errors[] = "Command failed: $cmd";
        break; // stop if any command fails
    }
    $out = []; // reset output for next command
}

// Response
header('Content-Type: application/json');
if (count($errors) > 0) {
    http_response_code(500);
    echo json_encode([
        "status" => "failed",
        "errors" => $errors,
        "output" => $output
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "output" => $output
    ]);
}
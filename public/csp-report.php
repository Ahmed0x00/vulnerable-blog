<?php
$report = file_get_contents('php://input');

$cspReport = json_decode($report, true);

header('Content-Type: application/json');
echo json_encode([
    'csp-report' => [
        'blocked-uri' => $cspReport['csp-report']['blocked-uri'],
        'disposition' => $cspReport['csp-report']['disposition'],
        'document-uri' => $cspReport['csp-report']['document-uri'],
        'effective-directive' => $cspReport['csp-report']['effective-directive'],
        'original-policy' => $cspReport['csp-report']['original-policy'],
        'referrer' => $cspReport['csp-report']['referrer'],
        'source-file' => $cspReport['csp-report']['source-file'],
        'status-code' => $cspReport['csp-report']['status-code'],
        'violated-directive' => $cspReport['csp-report']['violated-directive'],
    ]
]);
?>

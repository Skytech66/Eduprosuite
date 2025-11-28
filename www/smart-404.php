<?php
// Get the originally requested URL
$requestedUrl = $_SERVER['REQUEST_URI'];

// Output a page that first tries to reload the requested URL
echo '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Loading...</title>
<meta http-equiv="refresh" content="2; URL=' . htmlspecialchars($requestedUrl) . '" />
<style>
body { font-family: Arial; text-align: center; margin-top: 100px; }
</style>
</head>
<body>
<h2>Loading your page… please wait</h2>
<p>If the page does not load, you will be redirected to the dashboard shortly.</p>
<script>
// After 5 seconds, redirect to dashboard if still on error page
setTimeout(function(){
    window.location.href = "/dashboard";
}, 5000);
</script>
</body>
</html>';
exit;

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
body { 
    font-family: Arial; 
    text-align: center; 
    margin-top: 100px; 
}
</style>
</head>
<body>
<h2>Loading your page… please wait</h2>
<p>If the page does not load, you will be redirected shortly.</p>

<script>
// After 5 seconds, redirect to the correct dashboard location
setTimeout(function(){
    window.location.href = "/www/teachers/dashboard.php";
}, 5000);
</script>

</body>
</html>';

exit;
?>

<?php 

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../config/global.config.inc.php');
$file= 'mpay24-php/logs/mp_error.log';

$tid = $_GET['TID'];
$datum = date('Y-m-d H:i:s');

// Open the file to get existing content
$errorlog = file_get_contents($file);
// Append a new person to the file
$errorlog .= $datum.": ".$tid."\n";
// Write the contents back to the file
file_put_contents($file, $errorlog);

//echo 'error';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
</head>
<body>
<div>
	<h1>Bei der Bezahlung ist ein Fehler aufgetreten</h1>
	<a href="<?php echo APP_ROOT ?>addons/bewerbung/cis/bewerbung.php">Zurück zur Onlinebewerbung</a>
</div>
</body>
</html>



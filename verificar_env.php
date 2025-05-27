<?php
echo 'getenv: ' . getenv('DATABASE_URL') . "<br>";
echo 'apache_getenv: ' . apache_getenv('DATABASE_URL') . "<br>";
echo '_ENV: ' . ($_ENV['DATABASE_URL'] ?? 'No definido') . "<br>";
echo '_SERVER: ' . ($_SERVER['DATABASE_URL'] ?? 'No definido') . "<br>";
?>

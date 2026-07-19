<?php
require dirname(__DIR__) . '/includes/app.php';
logout();
header('Location: index.php');
exit;

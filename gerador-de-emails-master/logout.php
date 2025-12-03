<?php
require_once __DIR__ . '/auth/session.php';

Auth::logout();
header('Location: login.php');
exit;

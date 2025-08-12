<?php
include 'incloud/subscription-functions.php';
echo $_SESSION['user_id'];
var_dump(get_user_pending_subscription($_SESSION['user_id'],$pdo));
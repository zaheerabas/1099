<?php
require dirname(__DIR__) . '/app/bootstrap.php';
start_admin_session();
if (is_post()) {
    csrf_check();
    logout();
}
redirect('/admin/login.php');

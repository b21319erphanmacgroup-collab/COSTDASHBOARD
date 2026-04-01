<?php
if (session_id() == '') {
    session_name("DASHBOARD");
    session_save_path(join(DIRECTORY_SEPARATOR, array('D:', 'APM_Setup', 'Server', 'session', 'dashboard')));
    session_start();
}
?>
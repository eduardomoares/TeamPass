<?php
/**
 * @file          error.php
 * @author        Nils Laumaillé
 * @version       2.2.1
 * @copyright     (c) 2009-2013 Nils Laumaillé
 * @licensing     GNU AFFERO GPL 3.0
 * @link          http://www.teampass.net
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

@session_start();
if (!isset($_SESSION['CPM']) || $_SESSION['CPM'] != 1) {
    die('Hacking attempt...');
}

if (isset($_POST['session']) && $_POST['session'] == "expired") {
    //Include files
    require_once $_SESSION['settings']['cpassman_dir'].'/includes/settings.php';
    require_once $_SESSION['settings']['cpassman_dir'].'/includes/include.php';
    require_once $_SESSION['settings']['cpassman_dir'].'/sources/SplClassLoader.php';

    // connect to DB
    $db = new SplClassLoader('Database\Core', '../includes/libraries');
    $db->register();
    $db = new Database\Core\DbCore($server, $user, $pass, $database, $pre);
    $db->connect();

    // Include main functions used by TeamPass
    require_once 'sources/main.functions.php';

    // Update table by deleting ID
    if (isset($_SESSION['user_id'])) {
        $db->queryUpdate(
            "users",
            array(
                'key_tempo' => ''
           ),
            "id=".$_SESSION['user_id']
        );
    }

    //Log into DB the user's disconnection
    if (isset($_SESSION['settings']['log_connections']) && $_SESSION['settings']['log_connections'] == 1) {
        logEvents('user_connection', 'disconnection', $_SESSION['user_id']);
    }

    // erase session table
    $_SESSION = array();

    // Kill session
    session_destroy();
} else {
    echo '
    <div style="width:800px;margin:auto;">';
    if (@$_SESSION['error'] == 1000) {
        echo '
        <div class="ui-state-error ui-corner-all error" >'.$txt['error_not_authorized'].'</div>';
    } elseif (@$_SESSION['error'] == 1001) {
        echo '
        <div class="ui-state-error ui-corner-all error" >'.$txt['error_not_exists'].'</div>';
    } elseif (@$_SESSION['error'] == 1002) {
        echo '
        <div class="ui-state-error ui-corner-all error" style="text-align:center;" >'.$txt['index_session_expired'].'<br /><br /><a href="index.php" />'.$txt['home'] .'</a></div>';
    } elseif (@$_SESSION['error'] == 1003) {
        echo '
        <div class="ui-state-error ui-corner-all error" style="text-align:center;" >'.$txt['error_mcrypt_not_loaded'].'<br /><br /><a href="index.php" />'.$txt['home'] .'</a></div>';
    }
}

echo '
</div>';

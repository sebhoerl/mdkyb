<?php

include_once __DIR__ . '/../functions_user.php';
require_once __DIR__ . '/../../../../external/ExternalService.php';

function symfony_redirect_login()
{
    $service = ExternalService::getInstance();
    header('Location: ' . $service->generatePath('/login'));
    exit();
}

function symfony_get_phpbb_user()
{
    global $db;
    $service = ExternalService::getInstance();
    $member = $service->getUser();

    $id = $member->getForumId();
    if ($id == 0) {
        $id = user_add(array(
            'username'              => $member->getName(),
            'user_password'         => '',
            'user_email'            => $member->getEmail(),
            'group_id'              => 2,
            'user_timezone'         => 0.0,
            'user_lang'             => 'en',
            'user_type'             => USER_NORMAL,
            'user_ip'               => $_SERVER['REMOTE_ADDR'],
            'user_regdate'          => time(),
        ));

        $service->changeField('forumId', $id);
    }

    $sql = sprintf('SELECT * FROM %s WHERE user_id=%d', USERS_TABLE, $id);
    $result = $db->sql_query($sql);

    if ($row = $db->sql_fetchrow($result)) {
        return $row;
    } else {
        return array();
    }
}

function autologin_symfony()
{
    $service = ExternalService::getInstance();
    if (null !== ($user = $service->getUser())) {
        return symfony_get_phpbb_user();
    } else {
        symfony_redirect_login();
    }

    return array();
}

function validate_session_symfony($row)
{
    $service = ExternalService::getInstance();
    if (null === ($symfony = $service->getUser())) {
        symfony_redirect_login();
        return false;
    }

    return $symfony->getForumId() == $row['user_id'];
}

function logout_symfony()
{
    $service = ExternalService::getInstance();
    header('Location: ' . $service->generatePath('/logout'));
    exit();
}

function login_symfony($username, $password)
{
    $row = autologin_symfony();

    return array(
        'status' => empty($row) ? LOGIN_ERROR_EXTERNAL_AUTH : LOGIN_SUCCESS,
        'error_msg'=> '',
        'user_row'=> $row,
    );
}

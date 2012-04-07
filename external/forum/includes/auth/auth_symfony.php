<?php

include_once __DIR__ . '/../functions_user.php';

require_once  __DIR__ . '/../../../../app/bootstrap.php.cache';
require_once __DIR__ . '/../../../../app/autoload.php';

use Mdkyb\WebsiteBundle\Entity\Member;

function symfony_path($suffix)
{
    $uri = $_SERVER['REQUEST_URI'];
    $uri = preg_replace('@/forum(.*)$@', '', $uri);
    $uri .= $suffix;
    return $uri;
}

function symfony_redirect_login()
{
    header('Location: ' . symfony_path('/login'));
    exit();
}

function symfony_get_phpbb_user(Member $member)
{
    global $db;

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

        $sql = sprintf("UPDATE Member SET forumId =%d WHERE id='%d'", $id, $member->getId());
        $db->sql_query($sql);

        $member->setForumId($id);
        $token = unserialize($_SESSION['_symfony2']['attributes']['_security_secured']);
        $token->setUser($member);
        $_SESSION['_symfony2']['attributes']['_security_secured'] = serialize($token);
    }

    $sql = "SELECT * FROM " . USERS_TABLE . " WHERE user_id='$id'";
    $result = $db->sql_query($sql);

    if ($row = $db->sql_fetchrow($result)) {
        return $row;
    } else {
        return array();
    }
}

function symfony_get_user()
{
    session_write_close();
    $recover = session_name("PHPSESSID");
    session_start();

    if (isset($_SESSION['_symfony2'])) {
        $symfony = $_SESSION['_symfony2'];
        if (isset($symfony['attributes']))
        {
            $attributes = $symfony['attributes'];
            if (isset($attributes['_security_secured'])) {
                $token = $attributes['_security_secured'];
                $token = unserialize($token);
                $return = $token->getUser();
            }
        }
    }

    session_write_close();
    session_name($recover);
    session_start();

    return $return;
}

function autologin_symfony()
{
    if (null !== ($user = symfony_get_user())) {
        return symfony_get_phpbb_user($user);
    } else {
        symfony_redirect_login();
    }

    return array();
}

function validate_session_symfony($row)
{
    if (null === ($symfony = symfony_get_user())) {
        symfony_redirect_login();
        return false;
    }

    return $symfony->getForumId() == $row['user_id'];
}

function logout_symfony()
{
    header('Location: ' . symfony_path('/logout'));
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
    return autologin_symfony();
}

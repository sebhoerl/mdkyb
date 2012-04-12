// EXTERNAL MDKYB //

require_once __DIR__ . '/../../external/ExternalService.php';

/**
 * Tries to login a user based on the session information of Symfony
 *
 * @see Mediawiki documentation
 */
function symfony_login($user, &$result)
{
    wfSetupSession();

    $service = ExternalService::getInstance();

    // Redirect to login page if no user can be found
    if (null === ($member = $service->getUser())) {
        $service = ExternalService::getInstance();
        header('Location: ' . $service->generatePath('/login'));
        return true;
    }

    $id = $member->getWikiId();

    // Create new Mediawiki user if no ID is set
    if ($id == 0) {
        $title = Title::newFromText($member->getName());
        if (null === $title) {
            return true;
        }

        $name = $title->getText();
        if (!User::isValidUserName($name)) {
            return true;
        }

        $user->setName($name);
        $user->setPassword(User::randomPassword());
        $user->setEmail($member->getEmail());
        $user->setRealName($member->getName());
        $user->setToken();

        $user->addToDatabase();
        $user->saveSettings();

        $id = $user->getId();
        if ($id != 0) {
            $service->changeField('wikiId', $id);
        }
    } else {
        $user->setId($id);
        if (!$user->loadFromDatabase()) {
            return true;
        }
    }
    $user->saveToCache();

    $result = 1;
    return false;
}

/**
 * Handles MediaWiki's logout event.
 *
 * @see Mediawiki documentation
 */
function symfony_logout($user)
{
    $service = ExternalService::getInstance();
    header('Location: ' . $service->generatePath('/logout'));
}

$wgHooks['UserLoadFromSession'][] = 'symfony_login';
$wgHooks['UserLogout'][] = 'symfony_logout';

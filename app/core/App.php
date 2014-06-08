<?php namespace TheWall\Core;

use TheWall\Core\Helpers;
use TheWall\Core\Libs;

class App {

    public function run() {

        // starting the session

        Helpers\Session::init();

        /*
         | Check for Expired Session
         */

        if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 60)) {
            // The last activity was over 30 minutes ago.
            // unset
            session_unset();
            // destroy
            session_destroy();

        }
        $_SESSION['last_active'] = time(); // update last activity time stamp


        // TODO: Move this definition
        define('SECRET_KEY', 'testkey');

        // TODO: Find new place for this check!
        /*
         | Log in if persistance cookie is set!
         */

        // has persitance cookie?
        $cookie = isset($_COOKIE['persisted_session']) ? $_COOKIE['persisted_session'] : '';

        if ($cookie) {
            list ($user_id, $token, $mac) = explode(':', $cookie);
            if ($mac !== hash_hmac('sha256', $user_id . ':' . $token, SECRET_KEY)) {
                return false;
            }

            $usertoken = \PersistedSessionQuery::create()->findOneByUserId($user_id)->getToken();


            if ($usertoken === $token) {
                // regenerating session id, to kick off previous potential session hijackers
                Helpers\Session::regenerate();
                // Setting the current user's id in the session.
                Helpers\Session::set('user_id', $user_id);
                Helpers\Session::set('csrftoken', SHA1($user_id.Helpers\Config::get()->general->salt));
            }
        }

        // instantiating the router.
        $router = new Libs\Router();

        // setting the right path to the controllers dir.
        $router->setPath(__SITE_PATH . 'app/controllers');

        // running the loader
        $router->loader();
    }
    public function detect() {

    }
} 

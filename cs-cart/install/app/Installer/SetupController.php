<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Installer;

use Tygh;
use Tygh\Http;
use Tygh\Providers\SessionProvider;
use Tygh\Registry;

class SetupController
{
    /**
     * Setup index action
     *
     * @return array List of prepared variables
     */
    public function actionIndex()
    {
        // Check requirements
        $validator = new Validator;

        $cart_settings['main_language'] = App::DEFAULT_LANGUAGE;
        $setup = new Setup($cart_settings);
        $app = App::instance();

        $session_started = $validator->isSessionStarted();

        if ($session_started && !$app->getFromStorage('license_agreement')) {
            $params['dispatch'] = 'license';

            $app->run($params);
            exit(0);
        }

        $checking_result = [
            'extensions'                      => true,
            'session_started'                 => $session_started,
            'file_upload'                     => $validator->isFileUploadsSupported(),
            'safe_mode'                       => $validator->isSafeModeDisabled(),
            'php_version_supported'           => $validator->isPhpVersionSupported(),
            'session_auto_start'              => $validator->isSessionAutostartDisabled(),
            'file_system_writable'            => $validator->isFilesystemWritable(),
            'register_globals_disabled'       => $validator->isGlobalsDisabled(),
            'func_overload_acceptable'        => $validator->isFuncOverloadAcceptable(),
        ];

        $validator->isModeSecurityDisabled();
        $validator->isModRewriteEnabled();
        $validator->checkIfOpCacheSettingsIsConformAndSetNotification();

        $ext_check = $validator->validateExtensionsRequirements();
        $checking_result['extensions'] = $ext_check->isSuccess();

        $validator_result = true;
        foreach ($checking_result as $id => $validator_result) {
            if (empty($validator_result)) {
                $app->setNotification('E', $app->t('error'), $app->t('server_requirements_do_not_meet'), true, 'server_requirements');
                break;
            }
        }

        if (empty($checking_result['file_system_writable'])) {
            $app->setNotification('E', $app->t('error'), $app->t('check_files_and_folders_permissions'), true, 'file_permissions_section');
        }

        $this->_prepareHttpData();

        $languages = $setup->getLanguages();
        $available_themes = $setup->getAvailableThemes();
        $db_types = $setup->getSupportedDbTypes();

        $return = [
            'checking_result'           => $checking_result,
            'extensions'                => $ext_check->getData(),
            'show_requirements_section' => !$validator_result || $ext_check->getData(),
            'languages'                 => $languages,
            'available_themes'          => $available_themes,
            'db_types'                  => $db_types,
            'cart_settings'             => $cart_settings,
        ];

        if (filter_var(Setup::SURVEY_URL, FILTER_VALIDATE_URL)) {
            $logging = Http::$logging;
            Http::$logging = false;

            $survey = Http::get(Setup::SURVEY_URL, [], ['execution_timeout' => Setup::MAX_SURVEY_EXECUTION_TIMEOUT]);
            $survey = @json_decode($survey, true);

            Http::$logging = $logging;

            if ($survey) {
                $return['survey'] = $survey;
            }
        }

        return $return;
    }

    /**
     * Setup complete action
     *
     * @param array $params Request variables
     *
     * @return bool Always true
     */
    public function actionComplete($params = array())
    {
        $validator = new Validator;
        $app = App::instance();

        fn_define('CART_LANGUAGE', $app->getCurrentLangCode());
        fn_define('DESCR_SL', $app->getCurrentLangCode());

        $database = $app->getFromStorage('database_settings');

        if (!empty($database)) {
            $result = $validator->isMysqlSettingsValid($database['host'], $database['name'], $database['user'], $database['password'], $database['table_prefix'], $database['database_backend'], false);

            if ($result) {
                // Change current directory to prevent chdir(getcwd()) error while run session garbage collector
                chdir(Registry::get('config.dir.root') . '/');

                // Delete installer after store was installed.
                fn_rm(Registry::get('config.dir.root') . '/install');

                $password_change_timestamp = Tygh::$app['session']['admin_password_change_timestamp'] ?: 0;
                session_destroy();

                $this->_prepareHttpData();

                // Destroy installer session and start application session
                unset (Tygh::$app['session']);
                Tygh::$app->register(new SessionProvider());
                Tygh::$app['session']->start();

                $user_data = array (
                    'user_id' => 1,
                    'user_type' => 'A',
                    'area' => 'A',
                    'login' => 'admin',
                    'is_root' => 'Y',
                    'company_id' => 0,
                    'password_change_timestamp' => $password_change_timestamp
                );
                Tygh::$app['session']['auth'] = fn_fill_auth($user_data, array(), false, 'A');

                if (is_file(Registry::get('config.dir.root') . '/install/index.php')) {
                    Tygh::$app['session']['notifications']['installer'] = array(
                        'type' => 'W',
                        'title' => 'warning',
                        'message' => 'delete_install_folder',
                        'message_state' => 'S',
                        'new' => true,
                        'extra' => '',
                        'init_message' => true,
                    );
                }

                $redirect_url = Registry::get('config.http_location') . '/' . Registry::get('config.admin_index') . '?welcome';
                fn_redirect($redirect_url);
            }
        }

        fn_redirect('install/index.php');

        return true;
    }

    /**
     * Setup next_step action
     *
     * @param array<string, string>|null $cart_settings     Cart settings
     * @param array<string, string>|null $database_settings Database settings
     * @param array<string, string>|null $server_settings   Server settings
     * @param array<string, string>|null $survey            Survey answers
     *
     * @return bool  Always true
     */
    public function actionNextStep($cart_settings, $database_settings, $server_settings, $survey = [])
    {
        $app = App::instance();
        $validator = new Validator;

        if ($validator->validateAll(array_merge($cart_settings, $server_settings, $database_settings))) {
            $app->setInstallProgress('parts', 14);

            set_time_limit(0);

            if ($app->connectToDB(
                $database_settings['host'],
                $database_settings['name'],
                $database_settings['user'],
                $database_settings['password'],
                $database_settings['table_prefix'],
                $database_settings['database_backend']
            )) {

                $app->setToStorage('database_settings', $database_settings);

                define('CART_LANGUAGE', $cart_settings['main_language']);
                define('DESCR_SL', $cart_settings['main_language']);
                define('CART_PRIMARY_CURRENCY', App::PRIMARY_CURRENCY);
                define('CART_SECONDARY_CURRENCY', 'NULL'); // Need for cache_level
                $this->_prepareHttpData();

                $setup = new Setup($cart_settings, $server_settings, $database_settings, $this->isDemoInstall($cart_settings));
                $sAddons = new AddonsSetup;

                /* Notify Helpdesk about started installation */
                $setup->sendReport('start', !empty($survey) ? ['survey' => $survey] : []);

                /* Setup Scheme */
                $app->setInstallProgress('title', $app->t('setup_scheme'));
                $app->setInstallProgress('echo', $app->t('processing'), true);
                $app->setInstallProgress('step_scale', 2000);
                $setup->setupScheme();

                /* Setup Scheme Data */
                $app->setInstallProgress('step_scale', 1);
                $app->setInstallProgress('title', $app->t('setup_data'));
                $app->setInstallProgress('echo', $app->t('processing'), true);
                $app->setInstallProgress('step_scale', 5000);
                $setup->setupData();

                $setup->setSimpleMode();
                $setup->setCurrencies(CART_LANGUAGE);

                /* Setup Demo */
                if ($this->isDemoInstall($cart_settings)) {
                    $app->setInstallProgress('step_scale', 1);
                    $app->setInstallProgress('title', $app->t('setup_demo'));
                    $app->setInstallProgress('echo', $app->t('installing_demo_catalog'), true);
                    $app->setInstallProgress('step_scale', 5000);
                    // WARNING: during MVE demo data installation the script might be redirected
                    // and everything above will be executed again
                    $setup->setupDemo();
                } else {
                    $app->setInstallProgress('step_scale', 1);
                    $app->setInstallProgress('echo', $app->t('cleaning'), true);
                    $setup->clean();
                }

                $setup->setupUsers();


                if (!App::isTaskComplete('companies_setup')) {
                    /* Setup companies */
                    $app->setInstallProgress('step_scale', 1);
                    $app->setInstallProgress('title', $app->t('setup_companies'));
                    $app->setInstallProgress('echo', $app->t('processing'), true);

                    $companies_setup = $setup->setupCompanies();
                    App::setSetupTaskProgress('companies_setup', $companies_setup);
                }

                if (!App::isTaskComplete('languages_setup')) {
                    /* Setup Languages */
                    $app->setInstallProgress('step_scale', 1);
                    $app->setInstallProgress('title', $app->t('setup_languages'));
                    $app->setInstallProgress('echo', $app->t('processing'), true);
                    $app->setInstallProgress('step_scale', 1000);

                    $languages_setup = $setup->setupLanguages($this->isDemoInstall($cart_settings));
                    App::setSetupTaskProgress('languages_setup', $languages_setup);
                }

                if (!App::isTaskComplete('themes_setup')) {
                    $themes_setup = $setup->setupThemes();
                    App::setSetupTaskProgress('themes_setup', $themes_setup);
                }


                $setup->setupEmailTemplates();
                $setup->setupDocumentTemplates();

                if (!App::isTaskComplete('addons_setup')) {
                    /* Setup Add-ons */
                    $app->setInstallProgress('title', $app->t('setup_addons'));
                    $app->setInstallProgress('echo', $app->t('processing'), true);
                    $app->setInstallProgress('step_scale', 100);

                    $addons_setup = $sAddons->setup($this->isDemoInstall($cart_settings), array());
                    App::setSetupTaskProgress('addons_setup', $addons_setup);
                }

                if ($this->isDemoInstall($cart_settings)) {
                    $setup->setupDemoPost();
                }

                /* Setup HTTPS */
                $setup->setupSecurityProtocolSettings(defined('HTTPS'));

                /* Write config */
                $app->setInstallProgress('step_scale', 1);
                $app->setInstallProgress('echo', $app->t('writing_config'), true);
                $setup->writeConfig();

                /* Notify helpdesk about successful installation */
                $setup->sendReport('finish');

                if ($validator->checkScriptPathAllowedForOpcache()) {
                    $this->resetOpcache();
                }

                $redirect_url = Registry::get('config.http_location') . '/install/index.php?dispatch=setup.complete';

                if (Registry::get('runtime.comet')) {
                    Tygh::$app['ajax']->assign('force_redirection', $redirect_url);
                } else {
                    fn_redirect($redirect_url);
                }

                exit();
            }

        } else {
            if (Registry::get('runtime.comet')) {
                exit();

            } else {
                $params['dispatch'] = 'setup.index';
                $params['cart_settings'] = $cart_settings;
                $params['database_settings'] = $database_settings;
                $params['server_settings'] = $server_settings;

                $app->run($params);
            }
        }

        return true;
    }

    /**
     * Setup recheck action
     *
     * @param  array $cart_settings     Cart settings
     * @param  array $database_settings Database settings
     * @param  array $server_settings   Server settings
     * @param  array $addons            List of addons to be installed
     * @return bool  always true
     */
    public function actionRecheck($cart_settings, $database_settings, $server_settings, $addons)
    {
        $app = App::instance();

        $params['dispatch'] = 'setup.index';
        $params['cart_settings'] = $cart_settings;
        $params['database_settings'] = $database_settings;
        $params['server_settings'] = $server_settings;
        $params['addons'] = $addons;

        $app->run($params);

        return true;
    }

    /**
     * Corrects permissions of store files and folders
     *
     * @param  array $cart_settings     Cart settings
     * @param  array $database_settings Database settings
     * @param  array $server_settings   Server settings
     * @param  array $ftp_settings      FTP connection settings
     * @param  array $addons            List of addons to be installed
     * @return bool  Always true
     */
    public function actionCorrectPermissions($cart_settings, $database_settings, $server_settings, $ftp_settings, $addons)
    {
        $app = App::instance();
        $validator = new Validator;

        if (!empty($ftp_settings['ftp_hostname']) && !empty($ftp_settings['ftp_username']) && !empty($ftp_settings['ftp_password'])) {
            if (fn_ftp_connect($ftp_settings)) {
                $files = array (
                    'config.local.php' => 0666,
                    'images' => 0777,
                    'design' => 0777,
                    'var' => 0777
                );

                foreach ($files as $file => $perm) {
                    fn_ftp_chmod_file($file, $perm, true);
                }
            }
        }

        $validator->isFilesystemWritable(true);

        $params['dispatch'] = 'setup.index';
        $params['cart_settings'] = $cart_settings;
        $params['database_settings'] = $database_settings;
        $params['server_settings'] = $server_settings;
        $params['addons'] = $addons;

        $app->run($params);

        return true;
    }

    /**
     * Setup console action
     *
     * @param  array $cart_settings     Cart settings
     * @param  array $database_settings Database settings
     * @param  array $server_settings   Server settings
     * @param  array $addons            List of addons to be installed
     * @return bool  Result of setup
     */
    public function actionConsole($cart_settings, $database_settings, $server_settings, $addons = array())
    {
        $app = App::instance();

        $setup_result = 1; // return code for cli
        $validator = new Validator;

        if ($validator->validateAll(array_merge($cart_settings, $server_settings, $database_settings, $addons))) {
            if ($app->connectToDB(
                $database_settings['host'],
                $database_settings['name'],
                $database_settings['user'],
                $database_settings['password'],
                $database_settings['table_prefix'],
                $database_settings['database_backend']
            )) {
                define('CART_LANGUAGE', $cart_settings['main_language']);
                define('DESCR_SL', $cart_settings['main_language']);
                define('CART_PRIMARY_CURRENCY', App::PRIMARY_CURRENCY);
                define('CART_SECONDARY_CURRENCY', 'NULL'); // Need for cache_level

                $this->_prepareHttpData($server_settings);

                set_time_limit(0);

                $setup = new Setup($cart_settings, $server_settings, $database_settings, $this->isDemoInstall($cart_settings));
                $sAddons = new AddonsSetup;

                /* Notify Helpdesk about started installation */
                $setup->sendReport('start');

                $setup->setupScheme();
                $setup->setupData();

                $setup->setSimpleMode();
                $setup->setCurrencies(CART_LANGUAGE);

                if ($this->isDemoInstall($cart_settings)) {
                    $setup->setupDemo();
                } else {
                    $setup->clean();
                }

                $setup->setupUsers();

                $setup->setupCompanies();

                $setup->setupLanguages($this->isDemoInstall($cart_settings));

                $setup->setupThemes();
                $setup->setupEmailTemplates();
                $setup->setupDocumentTemplates();

                $sAddons->setup($this->isDemoInstall($cart_settings), $addons);

                if ($this->isDemoInstall($cart_settings)) {
                    $setup->setupDemoPost();
                }

                $license_number = !empty($cart_settings['license_number']) ? $cart_settings['license_number'] : '';
                $setup->setupLicense($license_number);

                /* Setup HTTPS */
                $setup->setupSecurityProtocolSettings(defined('HTTPS'));

                $setup->writeConfig();

                /* Notify helpdesk about successful installation */
                $setup->sendReport('finish');

                $app->setNotification('N', '', $app->t('successfully_finished'), true);

                $setup_result = 0;

                if ($validator->checkScriptPathAllowedForOpcache()) {
                    $this->resetOpcache();
                }
            }
        }

        return $setup_result;
    }

    /**
     * Returns flag of checking is demo require to be installed or not
     *
     * @param  array $cart_settings Cart settings
     * @return bool  True if demo require to be installed
     */
    public function isDemoInstall($cart_settings)
    {
        return (isset($cart_settings['demo_catalog']) && $cart_settings['demo_catalog'] == 'Y') ? true : false;
    }

    /**
     * Fills config array in Registry
     *
     * @param array $server_settings Server settings
     *
     * @return bool Always true
     */
    private function _prepareHttpData($server_settings = array())
    {
        if (empty($server_settings)) {
            $server_settings = array(
                'http_host' => $_SERVER['HTTP_HOST'],
                'http_path' => preg_replace('#/install$#', '', dirname($_SERVER['SCRIPT_NAME']))
            );
        }

        Registry::set('config.http_host', $server_settings['http_host']);
        Registry::set('config.http_path', $server_settings['http_path']);
        Registry::set('config.http_location', 'http://' . $server_settings['http_host'] . $server_settings['http_path']);

        return true;
    }

    /**
     * Resets opcache
     */
    private function resetOpcache()
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }
}

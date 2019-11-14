<?php
/**
 * Test suite bootstrap for ContactManager.
 *
 * This function is used to find the location of CakePHP whether CakePHP
 * has been installed as a dependency of the plugin, or the plugin is itself
 * installed as a dependency of an application.
 */
$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);

    throw new Exception("Cannot find the root of the application, unable to run tests");
};
$root = $findRoot(__FILE__);
unset($findRoot);

chdir($root);

if (file_exists($root . '/config/bootstrap.php')) {
    require $root . '/config/bootstrap.php';

    return;
}

// With PHP7.3 & CakePHP 3.5.0, StaticConfigTrait::parseDsn causes the error in preg_match
// (It has benn fixed in 3.5.1 https://github.com/cakephp/cakephp/commit/91475ccfa58948b2561ffd9631664c1c3edaf300)
// In place of using `db_dsn` uri, set db config with array.
if (getenv('DB') === 'mysql') {
    $dbConfig = [
        'className' => \Cake\Database\Connection::class,
        'driver' => \Cake\Database\Driver\Mysql::class,
        'host' => getenv('db_host'),
        'username' => getenv('db_user'),
        'database' => getenv('db_name'),
        'url' => null,
    ];
    \Cake\Datasource\ConnectionManager::setConfig('test', $dbConfig);
    \Cake\Datasource\ConnectionManager::setConfig('test_custom_i18n_datasource', $dbConfig);
    try {
        require $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';
    } catch (\BadMethodCallException $e) {
        if (strpos($e->getMessage(), 'Cannot reconfigure existing key') !== 0) {
            throw $e;
        }
    }
} else {
    require $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';
}

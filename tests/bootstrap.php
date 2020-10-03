<?php
declare(strict_types=1);

/**
 * Test suite bootstrap for ContactManager.
 *
 * This function is used to find the location of CakePHP whether CakePHP
 * has been installed as a dependency of the plugin, or the plugin is itself
 * installed as a dependency of an application.
 */

use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Datasource\ConnectionManager;

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

require $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';

$dbConfig = [
    'className' => Connection::class,
    'driver' => Mysql::class,
    'host' => getenv('db_host'),
    'username' => getenv('db_user'),
    'database' => getenv('db_name'),
    'url' => null,
];
ConnectionManager::drop('test');
ConnectionManager::setConfig('test', $dbConfig);
ConnectionManager::setConfig('test_custom_i18n_datasource', $dbConfig);

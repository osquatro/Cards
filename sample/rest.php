<?php

date_default_timezone_set('Europe/Dublin');
define("SQL_HOST", "127.0.0.1");
define("SQL_USER", "root");
define("SQL_PASS", "password");
define("SQL_DB", 'cards_strategy_visualizer');

require_once('../Osquatro/CommandProcessor.php');
require_once('../Osquatro/TableExporter.php');
require_once('../Osquatro/IDBAdapter.php');
require_once('../Osquatro/GIFEncoder.class.php');

require_once('src/DataBase.class.php');
require_once('src/DBAdapterImpl.php');

use Osquatro\cards\CommandProcessor;

use Osquatro\example\DB;
use Osquatro\example\DBAdapterImpl;

try {
    $db = new DB(SQL_USER, SQL_PASS, SQL_DB, SQL_HOST);
    $db->connect();
    $dbAdapter = new DBAdapterImpl($db);

    $processor = new CommandProcessor($dbAdapter, '../images', 'export', true);
    $res = $processor->processCommand($_REQUEST);
    echo json_encode($res);
} catch (Exception $ex) {
    echo json_encode(array('success' => false, 'message' => $ex->getMessage()));
}

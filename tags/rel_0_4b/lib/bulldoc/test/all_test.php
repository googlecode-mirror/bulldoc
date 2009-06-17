<?php
require_once (dirname(__FILE__).'/test_bootstrap.php');

define('TESTGROUPRUNNER', true);
define('ALLTESTRUNNER', true);

$groupAllTest = new TestSuite('All Bulldoc package test');
$groupAllTest->addFile(dirname(__FILE__).'/cases/edit/group_run.php');
$groupAllTest->addFile(dirname(__FILE__).'/cases/structure_content/group_run.php');

//require_once('cases/edit/group_run.php');


$groupAllTest->run(new HtmlReporter());
?>
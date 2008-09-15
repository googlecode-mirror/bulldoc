<?php
//Simpletest settings
require_once('simpletest/autorun.php');
define('TESTCASESFOLDER',realpath(dirname(__FILE__).'/../../'));
require_once(dirname(__FILE__).'/reporter/showpass_reporter.php');

require_once('coleso/fileoperation/std_directory_iterators.php');
require_once ('coleso/token/token.php');
require_once('simpletest_extensions/base_classes/base_coleso_test.php');



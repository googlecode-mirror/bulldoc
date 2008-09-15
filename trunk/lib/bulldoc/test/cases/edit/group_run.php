<?php
if (! defined('ALLTESTRUNNER')) {
  require_once('../../test_bootstrap.php');
  define('TESTGROUPRUNNER', true);
}

$editTest = new TestSuite('Edit Controllers');
$editTest->addFile('bulldoc/test/cases/edit/page_edit/controller_test.php');


if (! defined('ALLTESTRUNNER')) {
  $editTest->run(new HtmlReporter());
}
?>
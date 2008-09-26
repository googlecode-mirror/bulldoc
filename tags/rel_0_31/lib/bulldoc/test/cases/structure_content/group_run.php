<?php
if (! defined('ALLTESTRUNNER')) {
  require_once('../../test_bootstrap.php');
  define('TESTGROUPRUNNER', true);
}

$structureTest = new TestSuite('Book structure operations');
$structureTest->addFile('bulldoc/test/cases/structure_content/bookloader_test.php');
$structureTest->addFile('bulldoc/test/cases/structure_content/holder_test.php');
$structureTest->addFile('bulldoc/test/cases/structure_content/pagerender_test.php');
$structureTest->addFile('bulldoc/test/cases/structure_content/structure_iterators_test.php');


if (! defined('ALLTESTRUNNER')) {
  $structureTest->run(new HtmlReporter());
}
?>
<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id$
***********************************************************************************/

register_shutdown_function ('shutdownexit');
require_once(dirname(__FILE__).'/test_bootstrap.php');
require_once ('lib/simpletest_extensions/test_navigator/tree_walker.php');
renderTestTree(dirname(__FILE__).'/cases','cases/');

//-------------------------
function shutdownexit()
{
  exit();
}
?>
<?php
colesoApplication::loadMessages('bulldoc/messages');
require_once('coleso/toolkit/toolkit.php');
require_once('coleso/locale_strings/strings.php');

require_once('bulldoc/exceptions.php');
require_once('bulldoc/path_builder.php');
require_once('bulldoc/page_builder.php');
require_once('bulldoc/book_loader.php');

$configSet=colesoApplication::getConfigVal('/system/localConfig');

colesoApplication::setConfigVal('/bulldoc/themeDir',colesoApplication::getConfigVal('/system/docRoot').'workshop/themes/');
colesoApplication::setConfigVal('/bulldoc/themeUrl',colesoApplication::getConfigVal('/system/urlRoot').'workshop/themes/');
colesoApplication::setConfigVal('/bulldoc/output',colesoApplication::getConfigVal('/system/docRoot').'workshop/output/');
colesoApplication::setConfigVal('/bulldoc/rootUrl',colesoApplication::getConfigVal('/system/urlRoot'));
colesoApplication::setConfigVal('/bulldoc/systemTemplates',colesoApplication::getConfigVal('/system/docRoot').'workshop/themes/system/');
colesoApplication::setConfigVal('/bulldoc/rootIndexLevel',2);

//---------------------------------------------------------------------------------------------------
$customSource=$configSet->get('bulldoc::source',colesoApplication::getConfigVal('/system/docRoot').'workshop/source/');
colesoApplication::setConfigVal('/bulldoc/source',rtrim($customSource,'\\/').'/');

//---------------------------------------------------------------------------------------------------
$customBookShelf=$configSet->get('bulldoc::bookshelf',
                                  colesoApplication::getConfigVal('/system/docRoot').'workshop/source/bookshelf.yml');
colesoApplication::setConfigVal('/bulldoc/bookshelfConfig',$customBookShelf);

//---------------------------------------------------------------------------------------------------
$customTextProcessingClass=$configSet->get('bulldoc::defaultTextProcessingClass','docTemplateSet');
colesoApplication::setConfigVal('/bulldoc/textProcessingClass',$customTextProcessingClass);

//---------------------------------------------------------------------------------------------------
$customDefaultTheme=$configSet->get('bulldoc::defaultTheme','blueprint');
colesoApplication::setConfigVal('/bulldoc/defaultTheme',$customDefaultTheme);

//for standalone bulldoc application
colesoApplication::setConfigVal('/system/useStandaloneTheme',true);
?>

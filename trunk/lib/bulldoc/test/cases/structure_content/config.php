<?php
colesoApplication::setConfigVal('/bulldoc/systemTemplates',dirname(__FILE__).'/support/templates/system/');
colesoApplication::setConfigVal('/bulldoc/bookshelfConfig',dirname(__FILE__).'/support/workshop/bookshelf.yml');
colesoApplication::setConfigVal('/bulldoc/textProcessingClass','docTemplateSet');
colesoApplication::setConfigVal('/bulldoc/rootIndexLevel',2);

colesoApplication::setConfigVal('/bulldoc/themeDir',dirname(__FILE__).'/support/workshop/themes/');
colesoApplication::setConfigVal('/bulldoc/themeUrl','support/workshop/themes/');
colesoApplication::setConfigVal('/bulldoc/output',dirname(__FILE__).'/support/workshop/output/');
colesoApplication::setConfigVal('/bulldoc/source',dirname(__FILE__).'/support/workshop/source/');
?>

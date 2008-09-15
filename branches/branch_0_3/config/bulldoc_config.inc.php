<?php
colesoApplication::setConfigVal('/bulldoc/rootUrl',colesoApplication::getConfigVal('/system/urlRoot'));
colesoApplication::setConfigVal('/bulldoc/systemTemplates',colesoApplication::getConfigVal('/system/docRoot').'workshop/themes/system/');
colesoApplication::setConfigVal('/bulldoc/textProcessingClass','docTemplateSet');

colesoApplication::setConfigVal('/bulldoc/workshopDir',colesoApplication::getConfigVal('/system/docRoot').'workshop/');
colesoApplication::setConfigVal('/bulldoc/workshopUrl',colesoApplication::getConfigVal('/system/urlRoot').'workshop/');
colesoApplication::setConfigVal('/bulldoc/bookshelfConfig',colesoApplication::getConfigVal('/system/config'));
colesoApplication::setConfigVal('/bulldoc/defaultTheme','blueprint');
colesoApplication::setConfigVal('/bulldoc/rootIndexLevel',2);

//for standalone bulldoc application
colesoApplication::setConfigVal('/system/useStandaloneTheme',true);
?>

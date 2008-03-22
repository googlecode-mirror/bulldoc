SET RELEASE=0_2
SET DISTRIB_PATH=..\download\%RELEASE%\
mkdir %DISTRIB_PATH%

svn export http://bulldoc.googlecode.com/svn/branches/branch_%RELEASE% %DISTRIB_PATH%distrib
del ..\download\%RELEASE%\distrib\build_release.bat
CALL bulldoc bulldoc_site
@echo on
CALL bulldoc bulldoc
@echo on
CALL bulldoc bulldoc_chm
@echo on
D:\chm_factory\wshop\hhc workshop\output\bulldoc_chm\bulldoc_chm.hhp

cd workshop\output\bulldoc
zip -rq ..\..\..\%DISTRIB_PATH%bulldoc_doc_%RELEASE%.zip *
tar -cf ..\..\..\%DISTRIB_PATH%bulldoc_doc_%RELEASE%.tar *
gzip -fq ..\..\..\%DISTRIB_PATH%bulldoc_doc_%RELEASE%.tar
cd ..\..\..
copy workshop\output\bulldoc_chm\bulldoc_chm.chm %DISTRIB_PATH%\bulldoc_doc_%RELEASE%.chm

cd %DISTRIB_PATH%distrib
zip -rq ..\bulldoc_%RELEASE%.zip *
tar -cf ..\bulldoc_%RELEASE%.tar *
gzip -fq ..\bulldoc_%RELEASE%.tar

cd ..\..\..\bulldoc

rmdir %DISTRIB_PATH%distrib /Q /S


SET BRANCH=1_0
SET RELEASE=1_0

echo *** Build Russian Distributive

SET DISTRIB_PATH=..\download\%RELEASE%\
mkdir %DISTRIB_PATH%

svn export http://bulldoc.googlecode.com/svn/branches/branch_%BRANCH% %DISTRIB_PATH%distrib
del %DISTRIB_PATH%distrib\build_release.bat
del %DISTRIB_PATH%distrib\read.me
mkdir %DISTRIB_PATH%distrib\cache
mkdir %DISTRIB_PATH%distrib\workshop\output
rmdir %DISTRIB_PATH%distrib\lib\simpletest /Q /S
rmdir %DISTRIB_PATH%distrib\lib\simpletest_extensions /Q /S
rmdir %DISTRIB_PATH%distrib\lib\bulldoc\test /Q /S

copy build\rus\local_config.inc.php  %DISTRIB_PATH%distrib\local_config.inc.php /Y
copy build\rus\bookshelf.yml  %DISTRIB_PATH%distrib\workshop\source\bookshelf.yml /Y

CALL bulldoc bulldoc_site
@echo on
CALL bulldoc bulldoc_book
@echo on
CALL bulldoc bulldoc_chm
@echo on
D:\chm_factory\wshop\hhc workshop\output\bulldoc_chm\bulldoc_chm.hhp

cd workshop\output\bulldoc_book
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

echo *** Build English Distributive

SET DISTRIB_PATH=..\eng\download\%RELEASE%\
mkdir %DISTRIB_PATH%

svn export http://bulldoc.googlecode.com/svn/branches/branch_%BRANCH% %DISTRIB_PATH%distrib
del %DISTRIB_PATH%distrib\build_release.bat
del %DISTRIB_PATH%distrib\read.me
mkdir %DISTRIB_PATH%distrib\cache
mkdir %DISTRIB_PATH%distrib\workshop\output
rmdir %DISTRIB_PATH%distrib\lib\simpletest /Q /S
rmdir %DISTRIB_PATH%distrib\lib\simpletest_extensions /Q /S
rmdir %DISTRIB_PATH%distrib\lib\bulldoc\test /Q /S

copy build\eng\local_config.inc.php  %DISTRIB_PATH%distrib\local_config.inc.php /Y
copy build\eng\bookshelf.yml  %DISTRIB_PATH%distrib\workshop\source\bookshelf.yml /Y

CALL bulldoc bulldoc_site_eng
@echo on
CALL bulldoc bulldoc_book_eng
@echo on
CALL bulldoc bulldoc_chm_eng
@echo on
D:\chm_factory\wshop\hhc workshop\output\bulldoc_chm_eng\bulldoc_chm_eng.hhp

cd workshop\output\bulldoc_book_eng
zip -rq ..\..\..\%DISTRIB_PATH%bulldoc_doc_%RELEASE%_eng.zip *
tar -cf ..\..\..\%DISTRIB_PATH%bulldoc_doc_%RELEASE%_eng.tar *
gzip -fq ..\..\..\%DISTRIB_PATH%bulldoc_doc_%RELEASE%_eng.tar
cd ..\..\..
copy workshop\output\bulldoc_chm_eng\bulldoc_chm_eng.chm %DISTRIB_PATH%\bulldoc_doc_%RELEASE%_eng.chm

cd %DISTRIB_PATH%distrib
zip -rq ..\bulldoc_eng_%RELEASE%.zip *
tar -cf ..\bulldoc_eng_%RELEASE%.tar *
gzip -fq ..\bulldoc_eng_%RELEASE%.tar

cd ..\..\..\..\bulldoc

rmdir %DISTRIB_PATH%distrib /Q /S

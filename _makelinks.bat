cd /d %~dp0

rem WTP - NEW PROJECT INIT: makelinks v0.2

rem mklink /D __CONTENTS 	fileadmin\contents\
rem mklink /D __IMAGES 	fileadmin\templates\default\images\
rem mklink /D __TEMPLATES 	fileadmin\templates\default\

rem mklink /D typo3_src 	typo3_src-7.6.21
rem mklink /D typo3 		typo3_src\typo3
rem mklink index.php 		typo3_src\index.php


mkdir "F:\dropbox\WORK files\res - WPS - WTP3"
mklink /D ..\_res "F:\dropbox\WORK files\res - WPS - WTP3"
pause
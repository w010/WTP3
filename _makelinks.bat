cd /d %~dp0
rem mklink /D __CONTENTS 	fileadmin\contents\
rem mklink /D __IMAGES 	fileadmin\templates\default\images\
rem mklink /D __TEMPLATES 	fileadmin\templates\default\

rem mklink /D typo3_src 	typo3_src-7.6.16
rem mklink /D typo3 		typo3_src\typo3
rem mklink index.php 		typo3_src\index.php


mklink /D _res_dbox "F:\dropbox\WORK files\WTP3"
pause
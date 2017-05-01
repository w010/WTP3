cd /d %~dp0
rem mklink /D __CONTENTS 	fileadmin\contents\
rem mklink /D __IMAGES 	fileadmin\templates\default\images\
rem mklink /D __TEMPLATES 	fileadmin\templates\default\

mklink /D typo3_src 	typo3_src-7.6.16
mklink /D typo3 		typo3_src\typo3
mklink index.php 		typo3_src\index.php


mklink /D _materialy_dbox "F:\dropbox\WORK files\WTP3"
pause
cd /d %~dp0
mklink /D __CONTENTS 	fileadmin\contents\
mklink /D __IMAGES 	fileadmin\templates\default\images\
mklink /D __TEMPLATES 	fileadmin\templates\default\

mklink /D typo3_src 	typo3_src-7.4.0
mklink /D typo3 		typo3_src\typo3
mklink index.php 		typo3_src\index.php


mklink /D _materialy_dbox "F:\dropbox\WORK files\WTP3"
pause
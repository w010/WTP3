<?php
$extensionPath = t3lib_extMgm::extPath('direct_mail_userfunc');
$extensionClassesPath = $extensionPath . 'Classes/';
return array(
	// Compatibility with TYPO3 4.5
	'tx_directmailuserfunc_controller_wizard' => $extensionClassesPath . 'Controller/Wizard.php',
	'tx_directmailuserfunc_samples_testlist' => $extensionClassesPath . 'Samples/TestList.php',
	'tx_directmailuserfunc_samples_testlistextjs' => $extensionClassesPath . 'Samples/TestListExtjs.php',
	'tx_directmailuserfunc_samples_testlisttca' => $extensionClassesPath . 'Samples/TestListTca.php',
	'tx_directmailuserfunc_tca_parameters' => $extensionClassesPath . 'Tca/Parameters.php',
	'tx_directmailuserfunc_utility_itemsprocfunc' => $extensionClassesPath . 'Utility/ItemsProcFunc.php',
);

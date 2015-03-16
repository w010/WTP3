<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_wttcontent_pi1.php','_pi1','menu_type',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_wttcontent_pi2.php','_pi2','menu_type',1);



$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_ttcontent'] = array(
	// visuale stron do menu/sitemap
	'menuVisuals' => array(
		3 => array(
			//'menuVisualsUploadDir' => 'fileadmin/contents/pages_visuals/', // upload path dla visuali stron
			'menuVisualsUploadDir' => 'uploads/pics/', // upload path dla visuali stron
			'menuVisualsWidth0' => 120,
			'menuVisualsHeight0' => 60,
			'menuVisualsWidth1' => 190,
			'menuVisualsHeight1' => 112,
			
			'additional_html_after' => '<script type="text/javascript"> 
			
			$(document).ready(function() {
			    $("@@@menu_id@@@").carousel({
				    itemsPerPage: 5,
				    itemsPerTransition: 1, // number of items moved with each transition
				    noOfRows: 1,
				    nextPrevLinks: true,
				    pagination: false,
				    speed: \'fast\',
				    easing: \'swing\', // supports the jQuery easing plugin
				    nextText: \'\',
				    prevText: \'\'
				});
			});

			 </script>',
		),
	),

	'abstract_userfunc' => 'EXT:w_ttcontent/pi1/class.tx_wttcontent_pi1_abstract.php:tx_wttcontent_pi1_abstract->getRating',
);

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_ttcontent']['menuVisuals'][0] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_ttcontent']['menuVisuals'][3];
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_ttcontent']['menuVisuals'][1] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_ttcontent']['menuVisuals'][3];


?>
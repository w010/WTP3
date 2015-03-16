#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_wttcontent_visual blob NOT NULL,
);


CREATE TABLE tt_content (
	tx_wttcontent_visual_mode tinyint(3) DEFAULT '0' NOT NULL,
	tx_wttcontent_visual_size tinyint(3) DEFAULT '0' NOT NULL,
	menu_flexform text NOT NULL,
);

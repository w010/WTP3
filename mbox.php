<?php
/**
 * mbox email filesystem dump viewer
 * v0.2
 */

$mail = file_get_contents('mbox.log');

print '<html><body>';
if (isset($_GET['nobr']))
	# readable html
	print html_entity_decode(urldecode(quoted_printable_decode($mail)));
else
	# readable plain
	print nl2br(html_entity_decode(urldecode(quoted_printable_decode($mail))));

print '</body></html>';

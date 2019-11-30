// Get a list of meeting names
<?php

$dir_base = "/var/bigbluebutton/published/presentation/";

$dirs = array_filter(glob($dir_base . '*'), 'is_dir'); 
array_multisort(array_map('filemtime', $dirs), SORT_NUMERIC, SORT_DESC, $dirs); // Sort by file mtime

$counter = 0;

$counted = array();

foreach ($dirs as $dir) {

	$dir_trimmed = substr($dir, strlen($dir_base), 40);

	if (in_array($dir_trimmed, $counted)) {
		continue;
	}

	$xml = simplexml_load_file($dir . "/metadata.xml");

	$meeting_id = $xml -> id;
	$meeting_id_trimmed = substr($meeting_id, 0, 40);

	$meeting_name = $xml -> meta -> meetingName;
	$meeting_name_urlencoded = urlencode($meeting_name);

	$counter++;

	array_push($counted, $dir_trimmed);

	echo("<p><b>$counter - $meeting_name</b></p>\r\n");

}

?>

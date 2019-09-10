<?php

date_default_timezone_set('Asia/Tehran');
$url_base = "https://yourdomain.com/playback/presentation/2.0/playback.html?meetingId=";
$dir_base = "/var/bigbluebutton/published/presentation/";
$page_limit = 10; // Number of meetings in each page

$dirs = array_filter(glob($dir_base . '*'), 'is_dir'); 
array_multisort(array_map('filemtime', $dirs), SORT_NUMERIC, SORT_DESC, $dirs); // Sort by file mtime

// Show the first page if parameter is not set
if (!isset($_GET["page"])){
	$page = 1;
}else{
	$page = (int)$_GET["page"];
}

$meeting_count = count($dirs); // Total number of meetins (directories)

for ($i = 1; $i <= ceil($meeting_count / $page_limit); $i++){
	if ($i == $page){
		echo "<a style=\"color:#777\" href=\"?page=$i\">$i</a> ";
	}else{
		echo "<a href=\"?page=$i\">$i</a> ";
	}
}

$start = ($page - 1) * $page_limit;
$end = $page * $page_limit;
if ($end > $meeting_count){
	$end = $meeting_count;
}

for ($i = $start; $i < $end; $i++){
	$dir = $dirs[$i];

	$xml = simplexml_load_file($dir . "/metadata.xml");

	$meeting_id = $xml -> id;
	$meeting_name = $xml -> meta -> meetingName;
	$meeting_start_time = intval(substr($xml -> start_time, 0, 10)); // Extract 10 first digits (unix timestamp)
	$meeting_date = date("Y-m-d H:i", $meeting_start_time);
	$playback_size = (int)($xml -> playback -> size / 1000000); // Size in MB
	$playback_duration = (int)($xml -> playback -> duration / 1000); // Divide by 1000 to get duration in sec
		$playback_hours = (int)($playback_duration / 3600); 
		$playback_duration -= ($playback_hours * 3600);
		$playback_minutes = (int)($playback_duration / 60);
		$playback_seconds = $playback_duration - ($playback_minutes * 60);
		$playback_duration = sprintf("%02d:%02d:%02d", $playback_hours, $playback_minutes, $playback_seconds);

	$counter = $i + 1;
	echo("<p><b>$counter - $meeting_name</b> <a target=\"_blank\" href=\"$url_base$meeting_id\">playback</a><br>ID: $meeting_id<br>Date: $meeting_date<br>Duration: $playback_duration<br>Size: $playback_size MB</p>\r\n");
}
?>

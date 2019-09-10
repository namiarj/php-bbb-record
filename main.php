<?php

date_default_timezone_set('Asia/Tehran');

$dir_base = "/var/bigbluebutton/published/presentation/";
$dirs = array_filter(glob($dir_base . '*'), 'is_dir'); 
array_multisort(array_map('filemtime', $dirs), SORT_NUMERIC, SORT_DESC, $dirs); // Sort by file mtime
$url_base = "https://yourdomain.com/playback/presentation/2.0/playback.html?meetingId=";
$page_limit = 10; // Number of meetings in each page
$meeting_count = count($dirs); // Total number of meetins (directories)

// Default page is one if the page parameter is not set
if (!isset($_GET["page"]))
{
	$page = 1;
}else
{
	$page = (int)$_GET["page"];
}

if (! $_GET["meeting_name"])
{
	for ($i = 1; $i <= ceil($meeting_count / $page_limit); $i++)
	{
		if ($i == $page)
		{
			echo "<a style=\"color:#fff\" href=\"?page=$i\">$i</a> ";
		}else
		{
			echo "<a href=\"?page=$i\">$i</a> ";
		}
	}
}

if (isset($_GET["meeting_name"]))
{
	$start = 0;
	$end = $meeting_count;
}else
{
	$start = ($page - 1) * $page_limit;
	$end = $page * $page_limit;
	if ($end > $meeting_count)
	{
		$end = $meeting_count;
	}
}

$counter = 0;
for ($i = $start; $i < $end ;$i++)
{
	$dir = $dirs[$i];

	if (isset($search_meeting_id))
	{
		if ($search_meeting_id != substr($dir, strlen($dir_base), 40))
		{
			continue;
		}
	}

	$xml = simplexml_load_file($dir . "/metadata.xml");

	$meeting_id = $xml -> id;

	$meeting_name = $xml -> meta -> meetingName;
	if (isset($_GET["meeting_name"]))
	{
		if ($meeting_name != $_GET["meeting_name"])
		{
			continue;
		}else
		{
			$search_meeting_id = substr($dir, strlen($dir_base), 40);
		}

	}

	$bbb_origin = $xml -> meta -> {'bbb-origin'};

	$bbb_origin_server = $xml -> meta -> {'bbb-origin-server-name'};

	$meeting_start_time = intval(substr($xml -> start_time, 0, 10)); // Extract 10 first digits (unix timestamp)

	$meeting_date = date("Y-m-d H:i", $meeting_start_time);

	$playback_size = (int)($xml -> playback -> size / 1000000); // Size in MB

	$playback_duration = (int)($xml -> playback -> duration / 1000); // Divide by 1000 to get duration in sec

	$playback_hours = (int)($playback_duration / 3600); 
	$playback_hours = sprintf("%02d", $playback_hours);
	$playback_duration -= ($playback_hours * 3600);

	$playback_minutes = (int)($playback_duration / 60);
	$playback_minutes = sprintf("%02d", $playback_minutes);
	$playback_duration -= ($playback_minutes * 60);

	$playback_seconds = sprintf("%02d", $playback_duration);

	$meeting_num = $i + 1;
	$counter++;
	if (isset($_GET["meeting_name"]))
	{
		$meeting_num = $counter;
	}
	$meeting_name_urlencoded = urlencode($meeting_name);
	echo("\t\t\t\t<p><b>$meeting_num - $meeting_name</b><a target=\"_blank\" href=\"$url_base$meeting_id\"> playback</a> <a target=\"_blank\" href=\"?meeting_name=$meeting_name_urlencoded\">all meetings</a><br>ID: $meeting_id<br>Date: $meeting_date<br></span><br>Duration: $playback_hours:$playback_minutes:$playback_seconds<br>size: $playback_size MB<br>From: $bbb_origin_server $bbb_origin</p>\r\n");

}

?>

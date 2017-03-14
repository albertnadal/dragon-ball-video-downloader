<?php

define("TV3_DRAGON_BALL_AVAILABLE_EPISODES_URL", "http://www.tv3.cat/pvideo/AC_bbd_llista_MP4.jsp?format=flv&seccio=49274");
define("TV3_DRAGON_BALL_EPISODE_DETAILS_URL", "http://www.tv3.cat/pvideo/FLV_bbd_dadesItem_MP4.jsp?idint=");
define("LOCAL_DESTINATION_FOLDER", "/home/albert/bola_de_drac");

function clean_string($string)
{
	$string = strip_tags($string,"");
	$string = preg_replace('/[^A-Za-z0-9\s.\s-]/','',$string); 
	$string = str_replace( array( '.'), '', $string);
	return str_replace( array( ' '), '_', $string);
}

print "\nDownloading the list of available Dragon Ball episodes from TV3 web service... ";
$pageurl = TV3_DRAGON_BALL_AVAILABLE_EPISODES_URL;
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch, CURLOPT_URL, $pageurl );
$thecontents = curl_exec ( $ch );
curl_close($ch); 
print "[ done ]\n";

print "Parsing XML list... ";
$xml = simplexml_load_string($thecontents);
print "[ done ]\n";


foreach ($xml->item as $item)
{
	$id = $item->id;
        $title = $item->title;

	print "\nDownloading episode ($id)($title) data... ";
	$pageurl = TV3_DRAGON_BALL_EPISODE_DETAILS_URL."$id";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_URL, $pageurl );
	$thecontents = curl_exec ( $ch );
	curl_close($ch);
	print "[ done ]\n";

	$path = LOCAL_DESTINATION_FOLDER."/$id";

	if(!is_dir($path))
	{
		print "Making directory ($path)... ";
		mkdir($path, 0700);
		print "[ done ]\n";

		print "Parsing XML episode... ";
		$xml_episode = simplexml_load_string($thecontents);
		print "[ done ]\n";

		foreach ($xml_episode->videos->video as $video)
		{
			$video_url = $video->file;
			if($video_url != "")
			{
				$local_file = "$path/".(clean_string($title)).".mp4";

				print "Downloading video ($video_url) to ($local_file)... ";
				$cmd = "wget -q \"$video_url\" -O $local_file";
				exec($cmd);
				print "[ done ]\n";

				print "Recommended 20 seconds pause. Waiting...";
				sleep(20);
				print "[ done ];\n";
			}
		}
	}
}
print "\n";

?>

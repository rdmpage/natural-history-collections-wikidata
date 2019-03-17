<?php

// Fetch all repository codes from Wikispecies by doing a Category search


//----------------------------------------------------------------------------------------
function get($url, $user_agent='', $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);

	if ($content_type != '')
	{
		$opts[CURLOPT_HTTPHEADER] = array("Accept: " . $content_type);
	}
	
	if ($user_agent != '')
	{
		$opts[CURLOPT_USERAGENT] = $user_agent;
	}		
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	return $data;
}

$done = false;

$cmcontinue = '';

while (!$done)
{
	$url = 'https://species.wikimedia.org/w/api.php?action=query&list=categorymembers&cmtitle=Category:Repositories&format=json&cmlimit=500';
	
	$url .= '&cmcontinue=' . $cmcontinue;
	
	$json = get($url);

	//echo $json;
	
	$obj = json_decode($json);
	
	//print_r($obj);
	
	foreach ($obj->query->categorymembers as $c)
	{
		echo str_replace('Category:', '', $c->title) . "\n";
	}
	
	if (isset($obj->continue->cmcontinue))
	{
		$cmcontinue = $obj->continue->cmcontinue;
	}
	else
	{
		$done = true;
	}


}


?>

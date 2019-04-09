<?php

// Wikidata lookup functions

require_once (dirname(__FILE__) . '/fingerprint.php');
require_once (dirname(__FILE__) . '/lcs.php');
require_once (dirname(__FILE__) . '/utils.php');

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

//----------------------------------------------------------------------------------------
// Does wikidata have this Herbarium code for a P31 of herbarium?
function wikidata_herbarium_from_code($code)
{
	$item = '';
	
	// restrict to herbaria
	$sparql = 'SELECT * WHERE { ?herbarium wdt:P5858 "' . $code . '" . 
	?herbarium wdt:P31 wd:Q181916
	  }';
	  
	// any type of entity that has Herbarium Index code  	  
	$sparql = 'SELECT * WHERE { ?herbarium wdt:P5858 "' . $code . '" . 
	  }';
	  
	
	$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
	$json = get($url, '', 'application/json');
		
	if ($json != '')
	{
		$obj = json_decode($json);
		if (isset($obj->results->bindings))
		{
			if (count($obj->results->bindings) != 0)	
			{
				$item = $obj->results->bindings[0]->herbarium->value;
				$item = preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $item);
			}
		}
	}
	
	return $item;
}


//----------------------------------------------------------------------------------------
// Does wikidata have this code as a Biodiversity Repository ID?
function wikidata_repository_from_code($code)
{
	$item = '';
	
	$sparql = 'SELECT * WHERE { ?repository wdt:P4090 "' . $code . '" }';
	
	$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
	$json = get($url, '', 'application/json');
		
	if ($json != '')
	{
		$obj = json_decode($json);
		if (isset($obj->results->bindings))
		{
			if (count($obj->results->bindings) != 0)	
			{
				$item = $obj->results->bindings[0]->repository->value;
				$item = preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $item);
			}
		}
	}
	
	return $item;
}


//----------------------------------------------------------------------------------------
// Do we have this code as a Wikidata item linked to Wikispecies?
function wikidata_item_from_wikispecies_repository($code)
{
	$item = '';
	
	$sparql = 'SELECT * WHERE { VALUES ?article {<https://species.wikimedia.org/wiki/' . urlencode($code) . '> } ?article schema:about ?repository . }';
	
	$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
	$json = get($url, '', 'application/json');
	
	if ($json != '')
	{
		$obj = json_decode($json);
		
		//print_r($obj);
		
		if (isset($obj->results->bindings))
		{
			if (count($obj->results->bindings) == 1)	
			{
				$item = $obj->results->bindings[0]->repository->value;
				$item = preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $item);
			}
		}
	}
	
	return $item;
}

//----------------------------------------------------------------------------------------
// Do we have Wikidata item with this URL?
function wikidata_item_from_url($website)
{
	$item = '';
	
	$sparql = 'SELECT * WHERE { ?repository wdt:P856 <' . $website . '> }';
	
	$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
	$json = get($url, '', 'application/json');
	
	if ($json != '')
	{
		$obj = json_decode($json);
		
		//print_r($obj);
		
		if (isset($obj->results->bindings))
		{
			if (count($obj->results->bindings) == 1)	
			{
				$item = $obj->results->bindings[0]->repository->value;
				$item = preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $item);
			}
		}
	}
	
	return $item;
}

//----------------------------------------------------------------------------------------
// get Wikidata record
function get_wikidata_entity($qid)
{
	$entity = new stdclass;
	$entity->id = $qid;
	
	$entity->isPartOf = array();
	$entity->sameAs = array();
	$entity->code = array();	
	$entity->type = array();
	$entity->url = array();		
	
	$subject = '<http://www.wikidata.org/entity/' . $qid . '>';

	$url = 'https://www.wikidata.org/wiki/Special:EntityData/' . $qid . '.json';

	$json = get($url);

	$obj = json_decode($json);

	//echo $json;

	//echo json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
	
	// label
	$entity->name = array();
	foreach ($obj->entities->{$qid}->labels as $language => $label)
	{
		$entity->name[$language] = $label->value;
	}	
	
	// wikispecies
	foreach ($obj->entities->{$qid}->sitelinks as $sitelink => $link)
	{
		switch ($sitelink)
		{
			case 'specieswiki':
				$entity->code[] = $link->title;
				$entity->sameAs[] = $link->url;
				break;
				
			default:
				break;
		}
		$entity->name[$language] = $label->value;
	}	
	
	
	foreach ($obj->entities->{$qid}->claims as $p => $claims)
	{
		//echo $p . "\n";
		
		switch ($p)
		{
			// type
			case 'P31':	
				foreach ($claims as $claim)
				{
					switch($claim->mainsnak->datavalue->value->id)
					{
						// herbarium
						case 'Q181916':
							$entity->type[] = 'herbarium';
							break;
							
						// organization
						case 'Q43229':
							$entity->type[] = 'organization';
							break;
							
						// research university
						case 'Q15936437':
							$entity->type[] = 'research university';
							break;

						// public educational institution of the United States
						case 'Q23002039':
							$entity->type[] = 'public educational institution of the United States';
							break;
							
						// natural history museum
						case 'Q1970365':
							$entity->type[] = 'natural history museum';
							break;
							
						//museum
						case 'Q33506':
							$entity->type[] = 'museum';
							break;
							
						// research institute
						case 'Q31855':
							$entity->type[] = 'research institute';
							break;
							
						// university museum
						case 'Q866133':
							$entity->type[] = 'university museum';
							break;
							
						case 'Q17431399':
							$entity->type[] = 'national museum';
							break;
							
						case 'Q43501':
							$entity->type[] = 'zoo';
							break;
							
						case 'Q167346':
							$entity->type[] = 'botanical garden';
							break;
							
						case 'Q3918':
							$entity->type[] = 'university';
							break;
							
						case 'Q13226383':
							$entity->type[] = 'facility';
							break;
							
						default:
							$entity->type[] = $claim->mainsnak->datavalue->value->id;
							break;					
					}
				}			
				break;
				
			// Index Herbariorum code
			// Biorepository code
			case 'P5858':	
			case 'P4090':
				foreach ($claims as $claim)
				{
					$entity->code[] = $claim->mainsnak->datavalue->value;
				}			
				break;
				
			// collection or exhibition size
			case 'P1436':	
				foreach ($claims as $claim)
				{
					$entity->size = $claim->mainsnak->datavalue->value->amount;
				}			
				break;
				
				
			// part of relations
			case 'P195': // collection					
			case 'P361': // part of		
			case 'P749':// parent organization			
				foreach ($claims as $claim)
				{
					$entity->isPartOf[] = $claim->mainsnak->datavalue->value->id;
				}			
				break;
				
			// official website
			case 'P856':
				foreach ($claims as $claim)
				{
					$entity->url[] = $claim->mainsnak->datavalue->value;
				}			
				break;

			// coordinate location
			case 'P625':
				foreach ($claims as $claim)
				{
					$entity->latitude = $claim->mainsnak->datavalue->value->latitude;
					$entity->longitude = $claim->mainsnak->datavalue->value->longitude;
				}			
				break;
				
			// headquarters location
			case 'P159':
				foreach ($claims as $claim)
				{
					if (isset($claim->qualifiers))
					{
						foreach ($claim->qualifiers as $k => $qualifier)
						{
							switch ($k)
							{
								case 'P625':
									$entity->latitude = $qualifier[0]->datavalue->value->latitude;
									$entity->longitude = $qualifier[0]->datavalue->value->longitude;								
									break;
						
								default:
									break;
							}
						}
					}
				}			
				break;
			
				
			default:
				break;
		}

	}
	
	if (count($entity->isPartOf) == 0)
	{
		unset($entity->isPartOf);
	}
	if (count($entity->sameAs) == 0)
	{
		unset($entity->sameAs);
	}
	if (count($entity->url) == 0)
	{
		unset($entity->url);
	}
	if (count($entity->code) == 0)
	{
		unset($entity->code);
	}
	else
	{
		$entity->code = array_unique($entity->code);
	}
	if (count($entity->type) == 0)
	{
		unset($entity->type);
	}

	
	//print_r($entity);
	
	return $entity;
	
}	

//----------------------------------------------------------------------------------------
// Find in Wikidata
function wikidata_reconcile($text, $type = null, $properties = array(), $debug = false)
{
	$query = new stdclass;
	$query->query = $text;
	
	$query->limit = 3;
	
	if (isset($type))
	{
		$query->type = $type;
	}
	
	$query->properties = $properties;
	
	if ($debug)
	{
		print_r($query);
	}
	
	//echo json_encode($query);

	$json = get(
		'https://tools.wmflabs.org/openrefine-wikidata/en/api?query=' . urlencode(json_encode($query)),
		'Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405');

	//echo $json . "\n";
	
	$obj = json_decode($json);
	
	if ($debug)
	{
		print_r($obj);
	}
	
	$items = array();
	
	foreach ($obj->result as $result)
	{
		if ($result->match)
		{
			$items[] = $result->id;
		}
		else
		{
			// check
			$score = compare_two_strings($text, $result->name);
			//echo "score=$score\n";
		}	
	}
	
	return $items;
}

//----------------------------------------------------------------------------------------


// test
if (0)
{
	$code = 'LOJA';
	$code = 'UMMZ';
	$code = 'MPU';
	$code = 'KU'; // University
	$code = 'MO'; 
	$code = 'USNM'; 
	$code = 'TAIF'; // nope
	$code = 'LOJA';
	$code = 'MO'; 
	
	$code = 'UC'; 
	$code = 'UWBM';
	$code = 'AMNH';

	$code = 'AMNH';
	$code = 'NY';
	$code = 'HAL';
	$code = 'OXF';
	
	$code = 'PERTH';
	$code = 'SI';
	$code = 'NSW';
	$code = 'PH';

	$code = 'ARAN';
	$code = 'ZNU';
	$code = 'YU';
	$code = 'YUKU';
	
	$code = 'MZUSP';
	$code = 'LIL';
	$code = 'BM'; // no
	$code = 'ASU';
	$code = 'ZMUA';
	$code = 'MEXU';
	$code = 'POM'; // no
	$code = 'RSA';
	$code = 'UWO';
	$code = 'TTU';
	
	$code = 'MONZ';

	$code = 'AD'; 
	
	$code = 'MU';
	
	$item = wikidata_herbarium_from_code($code);
	
	echo "Wikidata from code $item\n";
	
	$item = wikidata_item_from_wikispecies_repository($code);

	echo "Wikidata from Wikispecies $item\n";
	
	
	/*
	$item = wikidata_repository_from_code($code);
	echo "Wikidata from repository $item\n";
	*/
	
	// details
	
	//$item = 'Q15908182';
	
	if ($item != '')
	{	
		get_wikidata_repository($item);
	}
	

}

if (0)
{
	$text = 'Northeast Forestry University';
	$type = 'Q3918'; // university
	
	$text = 'Herbarium Neapolitanum';
	$text = 'Università Degli Studi di Napoli Federico II Herbarium Neapolitanum';
	$text = 'Herbarium Neapolitanum Università Degli Studi di Napoli Federico II';
	$type = 'Q43229'; // organisation
	
	$text = 'Taiwan Forestry Research Institute';
	$type = null;
	
	$text = 'Australian National Herbarium';
	$type = 'Q181916'; // herbarium

	$text = 'University of Alaska Herbarium';
	$type = 'Q181916'; // herbarium
	
	$text = 'W S Turrell Herbarium Miami University';
	$type = null;
	
	$text = 'Taiwan Forestry Research Institute';
	$type = null;
	
	$text = 'Wartburg College';
	$type = null;
	
	$text = 'Museo Nacional de Historia Natural';
	$type = null;
	
	$text = 'Neuchatel Musee d\'Histoire Naturel';
	$text = 'Muséum d\'histoire naturelle de Neuchâtel';
	$type = null;
	
	
	//$text = 'Conservatoire et Jardin botaniques de la Ville de Genève';
	//$type = 'Q167346';
	
	// Properties don't exclude non-matching things from results,
	// but do affect order of results (those that have property appear earlier)
	// and value of "match" can be false if string matches by property doesn't
	$properties = array();
	
		
	if (0)
	{
		// Property by id
		$property = new stdclass;
		$property->pid = "P17"; // country
	
		$value = new stdclass;
		$value->id = "Q30"; // USA
		//$value->id = "Q17"; // Japan
		$value->id = "Q55"; // Netherlands
		$property->v = $value;
		$properties[] = $property;
	}
	
	if (0)
	{	
		// Property value as string
		$property = new stdclass;
		$property->pid = "P17";
		$property->v = "Uraguay";
		$properties[] = $property;
	}
		
	wikidata_reconcile($text, $type, $properties);
	

}

?>

<?php

// Match nodes 
// Get set of candidates (using various rules) then check matches and extract components.
// Should then add these to overall graph


require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

require_once(dirname(__FILE__) . '/components.php');
require_once(dirname(__FILE__) . '/compare_strings.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'grbio');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

//--------------------------------------------------------------------------------------------------
// Find candidates with same code
function same_code($code)
{
	$candidates = array();

	$db = NewADOConnection('mysqli');
	$db->Connect("localhost", 'root' , '' , 'grbio');
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	$db->EXECUTE("set names 'utf8'"); 

	$sql = 'SELECT * FROM nodes WHERE code="' . $code . '"';
	
	//echo $sql . "\n";

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$obj = new stdclass;
		
		$obj->id = $result->fields['id'];
		
		if ($result->fields['code'] != '')
		{
			$obj->code = $result->fields['code'];
		}
		
		if ($result->fields['item_type'] != '')
		{
			$obj->type = preg_split('/;\s*/', $result->fields['item_type']);
		}		

		if ($result->fields['country'] != '')
		{
			$obj->country = $result->fields['country'];
		}

		if ($result->fields['name'] != '')
		{
			$obj->name = $result->fields['name'];
		}
		
		// extended name?
		if ($result->fields['additional_name'] != '')
		{
			$obj->name .= ' ' . $result->fields['additional_name'];
		}
		
		if ($result->fields['wikispecies'] != '')
		{
			$obj->wikispecies = $result->fields['wikispecies'];
		}

		if ($result->fields['host'] != '')
		{
			$obj->host = $result->fields['host'];
		}	

		if ($result->fields['address'] != '')
		{
			$obj->address = $result->fields['address'];
		}	

		if ($result->fields['latitude'] != '')
		{
			$obj->latitude = $result->fields['latitude'];
		}	

		if ($result->fields['longitude'] != '')
		{
			$obj->longitude = $result->fields['longitude'];
		}	
	
		$candidates[] = $obj;

		$result->MoveNext();
	}
	
	return $candidates;	
}



//--------------------------------------------------------------------------------------------------
// Find candidates that match a search string
function fsearch($text)
{
	$candidates = array();

	$db = NewADOConnection('mysqli');
	$db->Connect("localhost", 'root' , '' , 'grbio');
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	$db->EXECUTE("set names 'utf8'"); 

	$sql = 'SELECT id, code, item_type, country, name, additional_name, wikispecies, host, address, latitude, longitude, MATCH (name) AGAINST ("' . addcslashes($text, '"') . '") AS score 
	FROM nodes AS score 
	WHERE MATCH (name) AGAINST ("' . addcslashes($text, '"') . '")
	ORDER BY score DESC LIMIT 10';
	
	//echo $sql . "\n";

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$obj = new stdclass;
		
		$obj->id = $result->fields['id'];
		
		if ($result->fields['code'] != '')
		{
			$obj->code = $result->fields['code'];
		}
		
		if ($result->fields['item_type'] != '')
		{
			$obj->type = preg_split('/;\s*/', $result->fields['item_type']);
		}		

		if ($result->fields['country'] != '')
		{
			$obj->country = $result->fields['country'];
		}

		if ($result->fields['name'] != '')
		{
			$obj->name = $result->fields['name'];
		}
		
		// extended name?
		if ($result->fields['additional_name'] != '')
		{
			$obj->name .= ' ' . $result->fields['additional_name'];
		}
		
		if ($result->fields['wikispecies'] != '')
		{
			$obj->wikispecies = $result->fields['wikispecies'];
		}

		if ($result->fields['host'] != '')
		{
			$obj->host = $result->fields['host'];
		}	

		if ($result->fields['address'] != '')
		{
			$obj->address = $result->fields['address'];
		}	

		if ($result->fields['latitude'] != '')
		{
			$obj->latitude = $result->fields['latitude'];
		}	

		if ($result->fields['longitude'] != '')
		{
			$obj->longitude = $result->fields['longitude'];
		}	
	
		$candidates[] = $obj;

		$result->MoveNext();
	}
	
	return $candidates;	
}



//--------------------------------------------------------------------------------------------------
// if strict then some failures to match mean we set score to 0 and bail out
function compare_candidates($one, $two, $strict = false)
{
	$score = 0;
	
	// Can't have same thing from same namespace
	$namespace1 = '';
	$namespace2 = '';
	
	if (preg_match('/^(?<namespace>jstor|Q|grbio|wikispecies|ncbi)/', $one->id, $m))
	{
		$namespace1 = $m['namespace'];
	}
	if (preg_match('/^(?<namespace>jstor|Q|grbio|wikispecies|ncbi)/', $two->id, $m))
	{
		$namespace2 = $m['namespace'];
	}
	if (($namespace1 != '') && ($namespace2 != ''))
	{
		if ($namespace1 == $namespace2)
		{
			if ($strict)
			{
				$score = 0;
				return $score;
			}
		}
	}
	
	// same code?
	if (isset($one->code) && isset($two->code))
	{
		if ($one->code == $two->code)
		{
			$score += 1;
		}
		else
		{
			if ($strict)
			{
				$score = 0;
				return $score;
			}
		}		
	}
	
	// same unique code?
	if (isset($one->unique_code) && isset($two->unique_code))
	{
		if ($one->unique_code == $two->unique_code)
		{
			$score += 1;
		}
		else
		{
			if ($strict)
			{
				$score = 0;
				return $score;
			}
		}		
	}
	
	
	// wikispecies link?
	if (isset($one->wikispecies) && isset($two->wikispecies))
	{
		if ($one->wikispecies == $two->wikispecies)
		{
			$score += 1;
		}
	}
	
	// same country?
	if (isset($one->country) && isset($two->country))
	{
		$c1 = $one->country;
		$c2 = $two->country;
		
		// normalise
		if ($c1 == 'USA')
		{
			$c1 = 'United States';
		}

		if ($c2 == 'USA')
		{
			$c2 = 'United States';
		}
		
		if ($c1 == $c2)
		{
			$score += 1;
		}
	}	
	
	// same internet host?
	if (isset($one->host) && isset($two->host))
	{
		if ($one->host == $two->host)
		{
			$score += 1;
		}
	}
	
	// same type?
	if (isset($one->type) && isset($two->type))
	{
	
		//print_r(array_intersect($one->type, $two->type));
	
		if (count(array_intersect($one->type, $two->type)) != 0)
		{
			$score += 1;
		}
		else
		{
			if ($strict)
			{
				$score = 0;
				return $score;
			}
		}
	}
	
	// strings match?
	if (isset($one->name) && isset($two->name))
	{
		if (do_strings_match($one->name, $two->name, 0.7))
		{
			$score += 1;
		}
		else
		{
			if ($strict)
			{
				$score = 0;
				return $score;
			}
		}
	}
	
	return $score;


}



//----------------------------------------------------------------------------------------
function go($candidates, $reason)
{
	$cutoff = 2; // minimum weight of an edge to be included in output
	$strict = true;


	$n = count($candidates);

	// initialise matrix
	$m = array();

	for ($i = 0; $i < $n; $i++)
	{
		$row = array();
		for ($j = 0; $j < $n; $j++)
		{
			$row[] = 0;
		}
		$m[] = $row;
	}

	// compare candidates using multiple criteria
	for ($i = 1; $i < $n; $i++)
	{
		for ($j = 0; $j < $i; $j++)
		{
			$score = compare_candidates($candidates[$i], $candidates[$j], $strict);
			$m[$i][$j] = $score;
			$m[$j][$i] = $score;
		}

	}

	// get maximum score
	$max_score = 0;
	for ($i = 0; $i < $n; $i++)
	{
		for ($j = 0; $j < $n; $j++)
		{
			$max_score  = max($max_score, $m[$i][$j]);
		}
	}


	// dump matrix
	if (0)
	{
		echo "\nRaw\n";
		for ($i = 0; $i < $n; $i++)
		{
			echo $i . '| ';
	
			for ($j = 0; $j < $n; $j++)
			{
				echo ' ' . $m[$i][$j];
			}
			echo "\n";
		}
	}

	echo "-- maximum score = $max_score\n";

	$cutoff = max($max_score - 1, 1);

	echo "-- cutoff = $cutoff\n";


	// filter based on cutoff
	// insures matrix comprises 0,1 and filters out low-scoring matches

	for ($i = 1; $i < $n; $i++)
	{
		for ($j = 0; $j < $i; $j++)
		{
			if ($m[$i][$j] >= $cutoff)
			{
				$m[$i][$j] = 1;
				$m[$j][$i] = 1;		
			}
			else
			{
				$m[$i][$j] = 0;
				$m[$j][$i] = 0;				
			}
		}
	}

	// dump matrix
	if (0)
	{
		for ($i = 0; $i < $n; $i++)
		{
			echo $i . '| ';
	
			for ($j = 0; $j < $n; $j++)
			{
				echo ' ' . $m[$i][$j];
			}
			echo "\n";
		}
	}

	// components
	$c = get_components($m);

	//print_r($c);

	// SQL

	foreach ($c as $component)
	{
		if (count($component) > 1)
		{
			$n = count($component);
		
			for ($i = 1; $i < $n; $i++)
			{
				for ($j = 0; $j < $i; $j++)
				{
					// echo $component[$j] . '-' . $component[$i] . "\n";
				
					$source = $candidates[$component[$j]]->id;
					$target = $candidates[$component[$i]]->id;
				
					echo 'REPLACE INTO edges(source, target, reason) VALUES("' . $source . '", "' . $target . '", "' . $reason . '");' . "\n";
				}
			}
		}
	}
}



//----------------------------------------------------------------------------------------


$reason = 'unknown';

// Candidates based on sharing same code
if (1)
{
	$reason = 'same code';

	$code = 'H';
	$code = 'MCZ';
	$code = 'BAA';

	$candidates = same_code($code);
}

// Candidates based on fulltext search
if (0)
{
	$reason = 'group by text search'; 
	
	$candidates = fsearch('Jardim Botânico Tropical');
	
	$candidates = fsearch('Universidad del Azuay');
}

if (1)
{
	print_r($candidates);
}


go($candidates, $reason);


?>

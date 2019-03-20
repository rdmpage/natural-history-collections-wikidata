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
// Find candidates that match a search string
function fsearch($text)
{
	$candidates = array();

	$db = NewADOConnection('mysqli');
	$db->Connect("localhost", 'root' , '' , 'grbio');
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	$db->EXECUTE("set names 'utf8'"); 

	$sql = 'SELECT id, code, item_type, country, name, additional_name, wikispecies, host, address, latitude, longitude MATCH (name) AGAINST ("' . addcslashes($text, '"') . '") AS score 
	FROM nodes AS score 
	WHERE MATCH (name) AGAINST ("' . addcslashes($text, '"') . '")
	ORDER BY score DESC LIMIT 10';

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
			$obj->type = $result->fields['item_type'];
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
function compare_candidates($one, $two)
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
			$score = 0;
			return $score;
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
			$score = 0;
			return $score;			
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
		if ($one->type == $two->type)
		{
			$score += 1;
		}
		else
		{
			$score = 0;
			return $score;			
		}
	}
	
	// strings match?
	if (isset($one->name) && isset($two->name))
	{
		if (do_strings_match($one->name, $two->name))
		{
			$score += 1;
		}
		else
		{
			$score = 0;
			return $score;			
		}
	}
	
	return $score;


}




//--------------------------------------------------------------------------------------------------

$cutoff = 2; // minimum weight of an edge to be included in output

$candidates = array();


// Candidates based on code
if (1)
{
	$code = 'ENCB';
	//$code = 'UCR';
	//$code = 'USM';
	//$code = 'AEI';
	
	$code = 'AAU';
	$code = 'CEPEC';
	
	$code = 'FT';
	
	$code = 'UT';
	$code = 'CIB';
	
	$code = 'VEN';

	$sql = 'SELECT * FROM nodes WHERE code="' . $code . '"';

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
			$obj->type = $result->fields['item_type'];
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
}

// Candidates based on fulltext search
if (0)
{
	$candidates = fsearch('Jardim Botânico Tropical');
}

print_r($candidates);

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
		$score = compare_candidates($candidates[$i], $candidates[$j]);
		$m[$i][$j] = $score;
		$m[$j][$i] = $score;
	}

}

// dump matrix
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

// filter based on cutoff
// insures matrix comprises 0,1 and filters out low-scoring matches
echo "\nFiltered\n";

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
for ($i = 0; $i < $n; $i++)
{
	echo $i . '| ';
	
	for ($j = 0; $j < $n; $j++)
	{
		echo ' ' . $m[$i][$j];
	}
	echo "\n";
}

// components
$c = get_components($m);

print_r($c);



/*
foreach ($candidates as $candidate)
{
	$sql = 'SELECT * FROM nodes WHERE code="' . $code . '"';
	
	//$sql .= ' AND item_type="herbarium"';
	
	$ids = array();
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$id = str_replace('%20', '_', $result->fields['id']);
		$ids[] = $id;
		$result->MoveNext();	
	}
	
	$n = count($ids);
	
	for ($i = 1; $i < $n; $i++)
	{
		echo 'REPLACE INTO edges(source, target, reason) VALUES("' . $ids[0] . '", "' . $ids[$i] . '", "same code");' . "\n";
	}
	

}

*/

?>


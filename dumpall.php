<?php

error_reporting(E_ALL ^ E_DEPRECATED);

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'grbio');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

// get all nodes, grouped by code

$codes = array();

$sql = 'SELECT DISTINCT code FROM nodes WHERE code IS NOT NULL ORDER BY code';

$sql = 'SELECT DISTINCT code FROM nodes WHERE id LIKE "jstor%" ORDER BY code';

$sql = 'SELECT DISTINCT code FROM nodes WHERE id LIKE "wikispecies%" ORDER BY code';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$codes[] = $result->fields['code'];
	$result->MoveNext();	

}

print_r($codes);

$letter = '';
$dir = '';

$counter = 0;

foreach ($codes as $code)
{
	$code = preg_replace('/[^\w]/', '-', $code);


	if ($letter != $code[0])
	{
		$letter = $code[0];
		$dir = 'dump/' . $letter;
		$dir = 'dumpj/' . $letter;
		$dir = 'dumpw/' . $letter;
		
		if (!file_exists($dir))
		{
			$oldumask = umask(0); 
			mkdir($dir, 0777);
			umask($oldumask);
		}
		
	
	}
	
	$html = '<html><body style="font-family:sans-serif;">';
		
	$html .= '<h2><a href="../' . $letter . '">' . $letter . '</a></h2>';
	$html .= '<h1>' . $code . '</h1>';
	
	$counter++;
	
	if (isset($codes[$counter]))
	{
		$next_code  = $codes[$counter];
		$next_code = preg_replace('/[^\w]/', '-', $next_code);
		
		$html .= '<p><a href="' . $next_code . '">Next</a></p>';
	}
	
	
	$html .= '<table border="1">';
	
	$sql = 'SELECT * FROM nodes WHERE code="' . $code . '"';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$html .= '<tr>';
	
	
		//switch ($result->fields['id'])
		
		if (preg_match('/wikispecies/', $result->fields['id']))
		{
			$html .= '<td><a href="https://species.wikimedia.org/wiki/' . str_replace('wikispecies', '', $result->fields['id']) . '" target="_new">' . $result->fields['id'] . '</td>';
		}

		if (preg_match('/jstor/', $result->fields['id']))
		{
			$html .= '<td><a href="https://plants.jstor.org/partner/' . str_replace('jstor', '', $result->fields['id']) . '" target="_new">' . $result->fields['id'] . '</td>';
		}

		if (preg_match('/grbioinstitution/', $result->fields['id']))
		{
			$html .= '<td><a href="http://dev.grscicoll.org/institution' . str_replace('grbioinstitution', '', $result->fields['id']) . '" target="_new">' . $result->fields['id'] . '</td>';
		}
		
		if (preg_match('/ncbi/', $result->fields['id']))
		{
			$html .= '<td><a href="https://www.ncbi.nlm.nih.gov/biocollections/' . str_replace('ncbi', '', $result->fields['id']) . '" target="_new">' . $result->fields['id'] . '</td>';
		}
		

		if (preg_match('/^Q/', $result->fields['id']))
		{
			$html .= '<td><a href="https://www.wikidata.org/wiki/' . $result->fields['id'] . '" target="_new">' . $result->fields['id'] . '</td>';
		}		
		
		//$html .= '<td>' . '<a href="https://empty-opal.glitch.me/?q=' . $result->fields['code'] . '" target="_new">' . $result->fields['code'] . '</td>';
		$html .= '<td><button onclick="document.getElementById(\'iframe\').src=\'//empty-opal.glitch.me/?q=' . $result->fields['code'] . '\'">' . $result->fields['code'] . '</button></td>';
		
		$html .= '<td>' . htmlentities($result->fields['unique_code']) . '</td>';		
		$html .= '<td>';
		$html .= $result->fields['name'];
		
		//$html .= ' <a href="https://en.wikipedia.org/w/index.php?search=' . $result->fields['name'] . '&title=Special%3ASearch&profile=advanced&fulltext=1" target="_new">Wikipedia</a>';
		$html .= ' <button onclick="document.getElementById(\'iframe\').src=\'//en.wikipedia.org/w/index.php?search=' . $result->fields['name'] . '&title=Special:Search&profile=advanced&fulltext=1\'">Wikipedia</button>';
		$html .= ' <button onclick="document.getElementById(\'iframe\').src=\'//www.wikidata.org/w/index.php?search=' . $result->fields['name'] . '&title=Special:Search&profile=advanced&fulltext=1\'">Wikidata</button>';
		$html .= ' <a href="https://www.google.com/search?q=' . $result->fields['name'] . '&title=Special%3ASearch&profile=advanced&fulltext=1" target="_new">Google</a>';
		
		
		$html .= '</td>';
		$html .= '<td>' . $result->fields['country'] . '</td>';
		$html .= '<td>' . $result->fields['address'] . '</td>';

		$html .= '<td>';
		if ($result->fields['url'] != '')
		{
			$html .= '<a href="' . $result->fields['url'] . '" target="_new">' . $result->fields['url'];
		}
		$html .= '</td>';
		
		$html .= '<td>' . $result->fields['item_type'] . '</td>';
		
		
		$html .= '</tr>';
		
		$result->MoveNext();	

	}	
	$html .= '</table>';
	
	
	$html .= '<iframe id="iframe" src="" width="100%" height="500px"></iframe>';
	
	
	$html .= '</body></html>';
	
	$filename = $dir . "/$code.html";
	
	file_put_contents($filename, $html);
	
	
	

	//exit();
}



?>


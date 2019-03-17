<?php

// Import grbio into nodes table

//----------------------------------------------------------------------------------------
// http://stackoverflow.com/a/5996888/9684
function translate_quoted($string) {
  $search  = array("\\t", "\\n", "\\r");
  $replace = array( "\t",  "\n",  "\r");
  return str_replace($search, $replace, $string);
}

//----------------------------------------------------------------------------------------

$filename = 'grbio/grbio_institutions_05_29_18.csv';

$headings = array();

$row_count = 0;

$file = @fopen($filename, "r") or die("couldn't open $filename");
		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgetcsv(
		$file_handle, 
		0, 
		translate_quoted(','),
		translate_quoted('"') 
		);
		
	$go = is_array($row);
	
	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;		
		}
		else
		{
			$obj = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if ($v != '')
				{
					if ($headings[$k] == 'Name')
					{
						$v = str_replace(' (' . $obj->Code . ')', '', $v);
					}
					
					$obj->{$headings[$k]} = $v;
				}
			}
		
			/*
			if (isset($obj->URL))
			{
				$parts = parse_url($obj->URL);
				
				$obj->host = $parts['host'];
				$obj->host = preg_replace('/^www\d?\./', '', $obj->host);
			
			}
			*/
		
			//print_r($obj);	
			
/*
(
    [nid] => 25840
    [published] => Yes
    [created] => 8 May 2018 - 4:46am EDT
    [changed] => 10 May 2018 - 10:29pm EDT
    [URL] => http://grbio.org/institution/bioparque-ukumar%C3%AD
    [Name of Institution] => Bioparque Ukumarí
    [Index Herbariorum Record] => Yes
    [Institutional Code/Acronym] => UKU
    [Additional Institution Names] => Sociedad Parque Temático de Flora y Fauna de Pereira
    [Institutional Discipline] => Agricultural Sciences & Natural Resources: Plant Sciences, Other
    [URL for main institutional website] => www.ukumari.co
    [URL for institutional specimen catalog] => sites.google.com/view/ukumari/
    [Cool URI] => http://grbio.org/cool/71qz-j715
    [Status of Institution: Active?] => Yes
    [Institutional Governance] => Public, Local: General
    [Institution Type] => Zoo or aquarium
    [Mailing Address 1] => nvarela@ukumari.co
    [Mailing Address 2] => nestorvarelaa@gmail.com
    [City/Town] => Pereira
    [State/Province] => Risaralda
    [Country] => Colombia
    [Primary Contact] => 25839
)
*/

			$entity = new stdclass;
			
			$address = array();
			
			foreach ($obj as $k => $v)			
			{
				if ($v != '')
				{
					switch ($k)
					{
						case 'URL':
							$entity->id = $v;
							$entity->id = 'grbio' . str_replace('http://grbio.org/', '', $entity->id);
							break;
				
				
						case 'Institutional Code/Acronym':
							$entity->code = $v;
							break;
						
						case 'Index Herbariorum Record':
							if ($v == 'Yes')
							{
								$entity->item_type = 'herbarium';
							}
							break;
							
						case 'Institution Type':
							$entity->item_type = strtolower($v);
							break;

						case 'Name of Institution':
							$entity->name = $v;
							break;

						case 'URL for main institutional website':
							$entity->url = $v;
							
							
							if (!preg_match('/^https?:\/\//', $entity->url))
							{
								$entity->url = 'http://' . $entity->url;
							}
		
		
							$parts = parse_url($entity->url);
							$entity->host = $parts['host'];
							$entity->host = preg_replace('/^www\d?\./', '', $entity->host);					
							break;
							
						case 'nid':
							$entity->nid = $v;
							break;

						case 'Cool URI':
							$entity->cool_uri = $v;
							break;
						
						case 'Country':
							$entity->country = $v;
							break;
							
						case 'Additional Institution Names':
							$entity->additional_name = $v;
							break;
							
						case 'Latitude':
							$entity->latitude = $v;
							break;

						case 'Longitude':
							$entity->longitude = $v;
							break;
							
						case 'Mailing Address 1':
							$address[] = $v;
							break;

						case 'Mailing Address 2':
							$address[] = $v;
							break;

						case 'Mailing Address 3':
							$address[] = $v;
							break;

						case 'City/Town':
							$address[] = $v;
							break;

						case 'State/Province':
							$address[] = $v;
							break;

						case 'Postal/Zip Code':
							$address[] = $v;
							break;
			
						default:
							break;
					}			
				}
			}
			
			// print_r($entity);
			
			if (count($address) > 0)
			{
				$entity->address = join(', ', $address);
				//print_r($entity);
				//exit();
			}
			
			
			// SQL
			if (1)
			{
			
				$keys = array();
				$values = array();
			
				foreach ($entity as $k => $v)					
				{
					$keys[] = $k;
					$values[] = '"' . addcslashes($v, '"') . '"';
				}
			
				echo 'REPLACE INTO nodes(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";			
			}

			
		}
	}	
	$row_count++;
}
?>


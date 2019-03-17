<?php

require_once(dirname(__FILE__) . '/fingerprint.php');
require_once(dirname(__FILE__) . '/lcs.php');

//----------------------------------------------------------------------------------------
function compare_two_strings($str1, $str2)
{
	$score = 0;

	// check
	$v1 = finger_print($str1);
	$v2 = finger_print($str2);	
	
	if (($v1 != '') && ($v2 != ''))
	{
		echo "v1: $v1\n";
		echo "v2: $v2\n";
		
		$lcs = new LongestCommonSequence($v1, $v2);
		$d = $lcs->score();
		
		echo "d=$d\n";
		
		$score = max($d / strlen($v1), $d / strlen($v2));
		
		//$score = $d / strlen($v1);
		
		echo "score=$score\n";
	}
	
	return $score;
}

//----------------------------------------------------------------------------------------
$stopwords = array(
'a', 'and', 'in', 'the', 'of',
'de', 'y',
'-'
);

//----------------------------------------------------------------------------------------
function optimizeSearchString($searchString) {
	global $stopwords;
    $wordsFromSearchString = preg_split('/,?\s+/u', $searchString);
    $finalWords = array_diff($wordsFromSearchString, $stopwords);
    return $finalWords;
}


$str1 = 'United States National Herbarium, Smithsonian Institution';
$str2 = 'United States National Herbarium, Department of Botany, Smithsonian Institution, Washington, District of Columbia, U.S.A.';
$str2 = 'Smithsonian Institution, Department of Botany United States National Herbarium';

$str1 = 'Herbarium, Botanische Staatssammlung München, München, Germany';
$str2 = 'Botanische Staatssammlung München Herbarium';

$str1 = 'Queensland Herbarium';
$str2 = 'Biosystematics Research Institute, Ottawa, Canada.';

$str1 = 'Nationaal Herbarium Nederland, Wageningen University branch, Wageningen, Netherlands';
$str2 = 'Nationaal Herbarium Nederland, Wageningen University Branch';

$str1 ='Herbarium University of California Riverside';
$str2 ='University of California, Riverside - Herbarium';
//$str2 = 'University of California, Riverside, California, USA.';
//$str2 = 'Universidad de Costa Rica, Museo de Zoologia';

//$str1 ='Escuela Nacional de Ciencias Biológicas, Instituto Politécnico Nacional Herbario';
//$str2 = 'Instituto Politécnico Nacional Herbario';

$parts1 = optimizeSearchString($str1);
$parts2 = optimizeSearchString($str2);

sort($parts1);
sort($parts2);

$parts1 = array_unique($parts1);
$parts2 = array_unique($parts2);

print_r($parts1);
print_r($parts2);

$s1 = join(' ', $parts1);
$s2 = join(' ', $parts2);



compare_two_strings($s1, $s2);

?>


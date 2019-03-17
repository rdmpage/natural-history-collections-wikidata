<?php

// compare two strings

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
		//echo "v1: $v1\n";
		//echo "v2: $v2\n";
		
		$lcs = new LongestCommonSequence($v1, $v2);
		$d = $lcs->score();
		
		//echo "d=$d\n";
		
		$score = max($d / strlen($v1), $d / strlen($v2));
		
		//$score = $d / strlen($v1);
		
		//echo "score=$score\n";
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

//----------------------------------------------------------------------------------------
function do_strings_match($str1, $str2, $threshold = 0.75)
{
	// remove stop words
	$parts1 = optimizeSearchString($str1);
	$parts2 = optimizeSearchString($str2);

	// sort 
	sort($parts1);
	sort($parts2);

	// remove duplicates
	$parts1 = array_unique($parts1);
	$parts2 = array_unique($parts2);

	// put back together
	$s1 = join(' ', $parts1);
	$s2 = join(' ', $parts2);

	$score = compare_two_strings($s1, $s2);
	
	return ($score >= $threshold);
}


?>


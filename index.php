<?php
include "search_functions.php";
global $availableSite;

@$searchwd = $_GET['searchwd'];
@$sort = $_GET['sort'];

print("available site : " . implode(', ', $availableSite) . " <form method='get' action='?'><input type='text' name='searchwd' value='$searchwd'/><input type='checkbox' name='sort' value=1 />按做种数排序<button type='submit'>go!</button></form>");

$search = "";
if($sort == 1)
	$search = "sort=7&type=desc";
else
	$search = "sort=10";
if($searchwd){	
	$searchwd = str_replace(' ', '+', $searchwd);
	$search .= "&search=$searchwd";
	print("<br /><table cellpadding='5'><tr><td class='colhead'>site</td><td class='colhead'>category</td><td class='colhead'>name</td><td class='colhead'>size</td><td class='colhead'>seeders</td><td class='colhead'>leechers</td><td class='colhead'>comments</td><td class='colhead'>completed</td><td class='colhead'>added</td>");
	$total = "";
	foreach($availableSite as $site){
		$searchSite = searchOtherSite($site);
		$res = $searchSite -> search($search);
		if(!is_array($res)){
			echo $site . " : " . $res . "<br />";
			continue;
		}	
		foreach($res as $t){
			print("<tr><td>" . $site . "</td><td>" . @$t['category'] . "</td><td><a target='_blank' href='http://" . $searchSite -> host . "/details.php?id=$t[tid]'><b>" . $t['name'] . "</b></a></td><td>" . $t['size'] . "</td><td><b>" . $t['seeders'] . "</b></td><td><b>" . $t['leechers'] . "</b></td><td><b>" . $t['comments'] . "</b></td><td><b>" . $t['completed'] . "</b></td><td>" . $t['added'] . "</td></tr>");
		}
		$total .= "<a target='_blank' href='".$searchSite->url."'>".$site . "</a>: 共" . count($res) . "个; ";
	}
	print("</table>" . $total);
}else{
	echo "no input!";
}


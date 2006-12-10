<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds_buildReport.php,v 1.39 2006/12/11 06:43:10 kevinlevy Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* @author Francisco Mancardi - 20050912 - remove unused code
* @author Kevin Levy - 20060603 - starting 1.7 changes
**/
require('../../config.inc.php');
require_once('common.php');
require_once('../functions/results.class.php');
require_once('../functions/users.inc.php');
testlinkInitPage($db);

$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'HTML';
$lastStatus = isset($_REQUEST['lastStatus']) ? $_REQUEST['lastStatus'] : null;

// statusForClass is used for results.class.php
// lastStatus is used to be displayed 
$statusForClass = 'a';
// TO-DO localize parameters passed from form
if ($lastStatus == "Passed"){
  $statusForClass = 'p';
 }
elseif ($lastStatus == "Failed"){
   $statusForClass = 'f';
}
elseif ($lastStatus == "Blocked"){
 $statusForClass = 'b';
}
elseif ($lastStatus == "Not Run"){
  $statusForClass = 'n';
}
elseif ($lastStatus == "Any"){
  $statusForClass = 'a';
}

$ownerSelected = isset($_REQUEST['owner']) ? $_REQUEST['owner'] : null;
$buildsSelected = isset($_REQUEST['build']) ? $_REQUEST['build'] : array();
$componentsSelected = isset($_REQUEST['component']) ? $_REQUEST['component'] : array();
$componentIds = null;
$componentNames = null;

for ($id = 0; $id < sizeOf($componentsSelected); $id++)
{
	list($suiteId, $suiteName) = split("\,", $componentsSelected[$id], 2);
	$componentIds[$id] = $suiteId;
	$componentNames[$id] = $suiteName;	
}

$keywordSelected = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : 0;
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpName = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : '';
$xls = ($format == 'EXCEL') ? true : false;
$buildsToQuery = -1;

if (sizeof($buildsSelected)) {
	$buildsToQuery = implode(",", $buildsSelected);
}

$tp = new testplan($db);
$re = new results($db, $tp, $componentIds, $buildsToQuery, $statusForClass, $keywordSelected, $ownerSelected);
$suiteList = $re->getSuiteList();
$flatArray = $re->getFlatArray();
$mapOfSuiteSummary =  $re->getAggregateMap();
$totals = $re->getTotalsForPlan();
$arrKeywords = $tp->get_keywords_map($tpID); 
$arrBuilds = $tp->get_builds($tpID); 
$mapBuilds = $tp->get_builds_for_html_options($tpID);

define('ALL_USERS_FILTER', null);
define('ADD_BLANK_OPTION', false);
$arrOwners = get_users_for_html_options($db, ALL_USERS_FILTER, ADD_BLANK_OPTION);
$smarty = new TLSmarty();
$smarty->assign('arrBuilds', $arrBuilds);
$smarty->assign('mapBuilds', $mapBuilds);
$smarty->assign('mapUsers',$arrOwners);
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('componentsSelected', $componentNames);
$smarty->assign('lastStatus', $lastStatus);
$smarty->assign('buildsSelected', $buildsSelected);
$smarty->assign('keywordsSelected', $keywordSelected);

if ($ownerSelected) {
$smarty->assign('ownerSelected', $arrOwners[$ownerSelected]);
}
$smarty->assign('totals', $totals);
$smarty->assign('testPlanName',$tpName);
$smarty->assign('testplanid', $tpID);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('suiteList', $suiteList);
$smarty->assign('flatArray', $flatArray);
$smarty->assign('mapOfSuiteSummary', $mapOfSuiteSummary);
$smarty->display('resultsMoreBuilds_report.tpl');
?>
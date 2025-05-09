<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 * 
 * Most of properties are extracted from Dojo Framework.
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

include_once '../tool/projeqtor.php';

$paramProject = pq_trim(RequestHandler::getId('idProject'));
$paramTeam = pq_trim(RequestHandler::getId('idTeam'));
$idOrganization = pq_trim(RequestHandler::getId('idOrganization'));
$paramActivityType = pq_trim(RequestHandler::getId('idActivityType'));
//$paramYear = RequestHandler::getYear('yearSpinner');
//$paramMonth = RequestHandler::getMonth('monthSpinner');
//$paramWeek = RequestHandler::getValue('weekSpinner');
$paramYear='';
if (array_key_exists('yearSpinner',$_REQUEST)) {
	$paramYear=$_REQUEST['yearSpinner'];
	$paramYear=Security::checkValidYear($paramYear);
};
$paramMonth='';
if (array_key_exists('monthSpinner',$_REQUEST)) {
	$paramMonth=$_REQUEST['monthSpinner'];
  $paramMonth=Security::checkValidMonth($paramMonth);
} else {
  $paramMonth="01";
}
$paramWeek='';
if (array_key_exists('weekSpinner',$_REQUEST)) {
	$paramWeek=$_REQUEST['weekSpinner'];
	$paramWeek=Security::checkValidWeek($paramWeek);
};

$paramStartDate=RequestHandler::getValue('startDate');
$paramEndDate=RequestHandler::getValue('endDate');

$user=getSessionUser();

$paramResource='';
if (array_key_exists('idResource',$_REQUEST)) {
  $paramResource=pq_trim($_REQUEST['idResource']);
  $paramResource = preg_replace('/[^0-9]/', '', pq_nvl($paramResource)); // only allow digits
  $canChangeResource=false;
  $crit=array('idProfile'=>$user->idProfile, 'scope'=>'reportResourceAll');
  $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
  if ($habil and $habil->id and $habil->rightAccess=='1') {
    $canChangeResource=true;
  }
  if (!$canChangeResource and $paramResource!=$user->id) {
    echo i18n('messageNoAccess',array(i18n('colReport')));
    if (!empty($cronnedScript)) goto end; else exit;
  } 
}

$periodType=RequestHandler::getValue('periodType'); // not filtering as data as data is only compared against fixed strings
$periodValue='';
if (array_key_exists('periodValue',$_REQUEST))
{
	$periodValue=$_REQUEST['periodValue'];
	$periodValue=Security::checkValidPeriod($periodValue);
}

// Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($idOrganization!="") {
  $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization',$idOrganization)) . '<br/>';
}
if ($paramTeam!="") {
  $headerParameters.= i18n("colIdTeam") . ' : ' . htmlEncode(SqlList::getNameFromId('Team', $paramTeam)) . '<br/>';
}
if ($paramActivityType!="") {
  $headerParameters.= i18n("colIdActivityType") . ' : ' . htmlEncode(SqlList::getNameFromId('ActivityType', $paramActivityType)) . '<br/>';
}
if ($periodType=='year' or $periodType=='month' or $periodType=='week') {
  $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';
}
//ADD qCazelles - Report fiscal year - Ticket #128
if ($periodType=='year' and $paramMonth!="01") {
  if (!$paramMonth ) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('month'))); // TODO i18n message
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  } else {
    $headerParameters.= i18n("startMonth") . ' : ' . i18n(date('F', mktime(0,0,0,$paramMonth,10))) . '<br/>';
  }
}
//END ADD qCazelles - Report fiscal year - Ticket #128
if ($periodType=='month') {
  $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}
if ( $periodType=='week') {
  $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
}
if ( $periodType=='date') {
	$headerParameters.= i18n("startDate") . ' : ' . htmlFormatDate($paramStartDate) . '<br/>';
	$headerParameters.= i18n("endDate") . ' : ' . htmlFormatDate($paramEndDate) . '<br/>';
}
// if ( $paramResource=='') {
//   $headerParameters.= i18n("colIdResource") . ' : ' . htmlEncode(SqlList::getNameFromId('Resource',$paramResource)) . '<br/>';
// }
if (isset($outMode) and $outMode=='excel') {
  $headerParameters.=pq_str_replace('- ','<br/>',Work::displayWorkUnit()).'<br/>';
}

include "header.php";
#florent ticket #4049
$specificResourceView = getListForSpecificRights('imputation',false,false,false);
$where="(".getAccesRestrictionClause('Activity',false,true,true,true) ." or idResource=". getSessionUser()->id . " or ( idProject in ".Project::getAdminitrativeProjectList()." and idResource in (".implode(',', array_keys($specificResourceView)).")) )"; 
//$where="1=1 ";
$where.=($periodType=='week')?" and week='" . $periodValue . "'":'';
$where.=($periodType=='month')?" and month='" . $periodValue . "'":'';
//CHANGE qCazelles - Report start month - Ticket #128
//Old
//$where.=($periodType=='year')?" and year='" . $periodValue . "'":'';
//New
if ($periodType=='year') {
  if (!$periodValue ) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('year'))); // TODO i18n message
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  }
  if ($paramMonth<10) $paramMonth='0'.intval($paramMonth);
  $where.=" and ((year='" . $periodValue . "' and month>='" . $periodValue.$paramMonth . "')".
          " or (year='" . ($periodValue + 1) . "' and month<'" . ($periodValue + 1) . $paramMonth . "'))";
}
if($periodType=='date'){
  $where.=" and (workDate>='".$paramStartDate."' and workDate<='".$paramEndDate."')";
}
//END CHANGE qCazelles - Report start month - Ticket #128
if ($paramProject!='') {
  #florent ticket #4049
  #$where.=  " and idProject in " . getVisibleProjectsList(true, $paramProject); 
  $where.=  " and idProject in " . getVisibleProjectsList(false, $paramProject); 
}
$where.=($paramResource!='')?" and idResource=" . $paramResource :'';
$order="";
//echo $where;
$work=new Work();
$lstWork=$work->getSqlElementsFromCriteria(null,false, $where, $order);
$result=array();
$projects=array();
$resources=array();
$sumProj=array();
$alreadyExistingActivity = array();
foreach ($lstWork as $work) {
  //if(!array_key_exists($work->idResource, $specificResourceView))continue;
  if ($paramActivityType and $work->refType=='Activity') {
    if (array_key_exists($work->refId, $alreadyExistingActivity))
      $act = $alreadyExistingActivity[$work->refId];
    else {
      $act = new Activity($work->refId);
      $alreadyExistingActivity[$work->refId] = $act;
    }
  }
  if (!$paramActivityType or (isset($act) and ($paramActivityType == $act->idActivityType) and $work->refType=='Activity')) {
    if (!array_key_exists($work->idResource, $resources)) {
      $resources[$work->idResource] = SqlList::getNameFromId('Resource', $work->idResource);
    }
    if (!array_key_exists($work->idProject, $projects)) {
      $projects[$work->idProject] = SqlList::getNameFromId('Project', $work->idProject);
    }
    if (!array_key_exists($work->idResource, $result)) {
      $result[$work->idResource] = array();
    }
    if (!array_key_exists($work->idProject, $result[$work->idResource])) {
      $result[$work->idResource][$work->idProject] = 0;
    }
    $result[$work->idResource][$work->idProject] += $work->work;
  }
}

if (checkNoData($result)) if (!empty($cronnedScript)) goto end; else exit;
// title
$newProject=array();
foreach ($projects as $id=>$name) {
  $newProject[SqlList::getFieldFromId('Project', $id, 'sortOrder').'-'.$id]=$name;
}

$projects=$newProject;
ksort($projects);
foreach ($projects as $id=>$name) {
  $idExplo=pq_explode('-',$id);
  $id=$idExplo[1];
  $sumProj[$id]=0;  
}
asort($resources);
//gautier #4342
if($idOrganization){
  $orga = new Organization($idOrganization);
  $listOrga = $orga->getRecursiveSubOrganizationsFlatList(false,true);
  $listResOrg = array();
  foreach ($listOrga as $id=>$org){
    $org = new Organization($id);
    $listResOrg += $org->getResourcesOfOrganizationsListAsArray();
  }
  $listResOrg = array_flip($listResOrg);
  foreach ($resources as $idR=>$nameR){
    if(! in_array($idR, $listResOrg))unset($resources[$idR]);
  }
}

foreach ($resources as $idR=>$nameR) {
  if ($paramTeam) {
    $res=new ResourceAll($idR);//florent ticket #5038
  }
  if (!$paramTeam or $res->idTeam==$paramTeam) {
    foreach ($projects as $idP=>$nameP) {
      $idExplo=pq_explode('-',$idP);
      $idP=$idExplo[1];
      if (array_key_exists($idR, $result)) {
        if (array_key_exists($idP, $result[$idR])) {
          $val=$result[$idR][$idP];
          $sumProj[$idP]+=$val;
        }
      }
    }
  }
}
$nbProj=0;
$hasCode=false;
$arrayCodes=array();
foreach ($projects as $id=>$name) {
  $idExplo=pq_explode('-',$id);
  $idS=$idExplo[1];
  if($sumProj[$idS] != 0) {
    $cdProj=SqlList::getFieldFromId('Project',$idS,'projectCode');
    $arrayCodes[$id]=($cdProj)?$cdProj:'&nbsp;';
    if (pq_trim($cdProj)!='') $hasCode=true;
    $nbProj+=1;
  }
}
if($nbProj != 0)
$colWidth=round(80/$nbProj);
else
$colWidth=round(80/1);
$rowspan=($hasCode)?'3':'2';
echo '<table style="width:90%;" align="center" '.excelName().'>';
echo '<tr>';
echo '<td style="width:10%" class="reportTableHeader" rowspan="'.$rowspan.'" '.excelFormatCell('header',20).'>' . i18n('Resource') . '</td>';
echo '<td style="width:80%" colspan="' . $nbProj . '" class="reportTableHeader" '.excelFormatCell('header').'>' . i18n('Project') . '</td>';
echo '<td style="width:10%" class="reportTableHeader" rowspan="'.$rowspan.'" '.excelFormatCell('header',10).'>' . i18n('sum') . '</td>';
echo '</tr><tr>';
foreach ($projects as $id=>$name) {
  $idExplo=pq_explode('-',$id);
  $id=$idExplo[1];
  if($sumProj[$id] != 0) {
    echo '<td style="width:'.$colWidth.'%" class="reportTableColumnHeader" '.excelFormatCell('subheader',20).'>' . htmlEncode($name) . '</td>';
  }
}
echo '</tr>';
if ($hasCode) {
echo '<tr>';
foreach ($projects as $id=>$name) {
  if (isset($arrayCodes[$id])) {
    echo '<td style="width:'.$colWidth.'%" class="reportTableColumnHeader" '.excelFormatCell('subheader',20).'>' . $arrayCodes[$id] . '</td>';
  }
}
echo '</tr>';
}

$sum=0;
foreach ($resources as $idR=>$nameR) {
	if ($paramTeam) {
		$res=new ResourceAll($idR);//florent ticket #5038
	}
  if (!$paramTeam or $res->idTeam==$paramTeam) {
		$sumRes=0;
	  echo '<tr><td style="width:10%" class="reportTableLineHeader" '.excelFormatCell('rowheader').'>' . htmlEncode($nameR) . '</td>';
	  foreach ($projects as $idP=>$nameP) {
	    
      $idExplo=pq_explode('-',$idP);
      $idP=$idExplo[1];
    if($sumProj[$idP] != 0){
	    echo '<td style="width:' . $colWidth . '%" class="reportTableData" '.excelFormatCell('data',null,null,null,null,null,null,null,(($val)?'work':null)).'>';
	    if (array_key_exists($idR, $result)) {
	      if (array_key_exists($idP, $result[$idR])) {
	        $val=$result[$idR][$idP];
	        echo Work::displayWorkWithUnit($val);
	        $sumRes+=$val; 
	        $sum+=$val;
	      } 
	    }
	    echo '</td>';
	  }
  }
	  echo '<td style="width:20%" class="reportTableColumnHeader" '.excelFormatCell('subheader').'>' . Work::displayWorkWithUnit($sumRes) . '</td>';
	  echo '</tr>';
  }
}
echo '<tr><td class="reportTableHeader" '.excelFormatCell('header').'>' . i18n('sum') . '</td>';
if ($nbProj == 0)
   echo '<td class="reportTableHeader" '.excelFormatCell('subheader').'>' . "" . '</td>';

foreach ($projects as $id=>$name) {
  $idExplo=pq_explode('-',$id);
  $id=$idExplo[1];
  if($sumProj[$id] != 0)
  echo '<td class="reportTableColumnHeader" '.excelFormatCell('subheader').'>' . Work::displayWorkWithUnit($sumProj[$id]) . '</td>';
}
echo '<td class="reportTableHeader" '.excelFormatCell('header').'>' . Work::displayWorkWithUnit($sum) . '</td></tr>';
echo '</table>';

end:

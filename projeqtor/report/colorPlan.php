<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
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

//echo "colorPlan.php";
include_once '../tool/projeqtor.php';

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=pq_trim($_REQUEST['idProject']);
  Security::checkValidId($paramProject);
}
$idOrganization = pq_trim(RequestHandler::getId('idOrganization'));
$paramTeam='';
if (array_key_exists('idTeam',$_REQUEST)) {
  $paramTeam=pq_trim($_REQUEST['idTeam']);
  Security::checkValidId($paramTeam);
}
$paramYear='';
if (array_key_exists('yearSpinner',$_REQUEST)) {
	$paramYear=$_REQUEST['yearSpinner'];
	$paramYear=Security::checkValidYear($paramYear);
};
$paramMonth='';
if (array_key_exists('monthSpinner',$_REQUEST)) {
	$paramMonth=$_REQUEST['monthSpinner'];
  $paramMonth=Security::checkValidMonth($paramMonth);
};
$paramWeek='';
if (array_key_exists('weekSpinner',$_REQUEST)) {
	$paramWeek=$_REQUEST['weekSpinner'];
	$paramWeek=Security::checkValidWeek($paramWeek);
};
$scale='proportional';
if (array_key_exists('scale',$_REQUEST) and $_REQUEST['scale']=='same') {
	$scale='same';
}

$paramShowAdminProj=RequestHandler::getBoolean('showAdminProj');

$user=getSessionUser();
$printPreview = (RequestHandler::getValue('outMode') == 'html');
$printMode = RequestHandler::getValue('outMode');

$periodType=$_REQUEST['periodType']; // not filtering as data as data is only compared against fixed strings
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
if ($periodType=='year' or $periodType=='month' or $periodType=='week') {
  $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';
  
}
if ($periodType=='month') {
  $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}
if ( $periodType=='week') {
  $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
}
$nbMonths=1;
if ($periodType=='month' and isset($_REQUEST['includeNextMonth'])) {
  $nbMonths=2;
  $headerParameters.= i18n("colIncludeNextMonth").'<br/>';
}
if ($paramShowAdminProj) {
  $headerParameters.= i18n("colShowAdminProj").'<br/>';
}

$headerParameters.=i18n("colFormat"). ' : ' . i18n('scale'.pq_ucfirst($scale)).'<br/>';

include "header.php";

$initParamMonth=$paramMonth;
// LOOP FOR SEVERAL MONTHS
for ($cptMonth=0;$cptMonth<$nbMonths;$cptMonth++) {
  if ($periodType=='month') {
    $paramMonth=intval($initParamMonth)+$cptMonth;
  if ($paramMonth>12) {$paramYear+=1;$paramMonth=1;}
    if ($paramMonth<10) $paramMonth='0'.$paramMonth;
    $periodValue=$paramYear.$paramMonth;
  }
  
$where=getAccesRestrictionClause('Resource',false,false,true,true);
//$where="1=1";
$where='('.$where.' or idProject in '.Project::getAdminitrativeProjectList().')';

$where.=($periodType=='week')?" and week='" . $periodValue . "'":'';
$where.=($periodType=='month')?" and month='" . $periodValue . "'":'';
$where.=($periodType=='year')?" and year='" . $periodValue . "'":'';
if ($paramProject!='') {
//   $where.=  "and ( idProject in " . getVisibleProjectsList(true, $paramProject) ;
//   if ($paramShowAdminProj) {
//     $where.= "or idProject in ".Project::getAdminitrativeProjectList();
//   }
//   $where.= ")";
}
$order="";
$work=new Work();
$lstWork=$work->getSqlElementsFromCriteria(null,false, $where, $order);
$result=array();
$projects=array(); 
$projectsColor=array();
$resources=array();
$resourcesTeam=array();
$resourceCapacity=array();
//gautier #2441
$idRessource=getSessionUser()->id;
$resss=new ResourceAll($idRessource);
$resourcesFull = array();
$resourcesAffect= array();
$resourcesToShow = array();
$specific='imputation';
$commonElement = getListForSpecificRights($specific,true);
$resourcesToShow=$commonElement;
$refIdCell = '';
$refTypeCell = '';
//$commonElement = $table;
//no parameters
//project

if($paramProject){
  $proj=new Project($paramProject);
  $listProj=$proj->getRecursiveSubProjectsFlatList(false,true);
  if ($paramShowAdminProj) {
    foreach (Project::getAdminitrativeProjectList(true) as $idP=>$nameP) {
      $listProj[$idP]=$nameP;
    }
  }
  $resourcesAffect = SqlList::getListWithCrit('Affectation', array('idProject'=>array_keys($listProj)),'idResource');
  $resourcesProject = SqlList::getListWithCrit('ResourceAll', array('id'=>$resourcesAffect));
  $resourcesToShow = array_intersect($resourcesToShow,$resourcesProject);
} else {
  $resourcesNotClose = SqlList::getListWithCrit('ResourceAll', array('idle'=>'0'));
  $resourcesToShow = array_intersect($resourcesToShow,$resourcesNotClose);
}

$selectedDateStart = "$paramYear-$paramMonth-".lastDayOfMonth($paramMonth,$paramYear);
$resourcesNotHereBeforeStart = SqlList::getListWithCrit('ResourceAll',"idle=0 and startdate>'$selectedDateStart'");
foreach ($resourcesNotHereBeforeStart as $res) {
  foreach($resourcesToShow as $key=>$value) {
    if ($res == $value) {
      unset($resourcesToShow["$key"]);
    }
  }
}
$selectedDateEnd = "$paramYear-$paramMonth-01";
$resourcesNotHereAfterEnd = SqlList::getListWithCrit('ResourceAll',"idle=0 and enddate<'$selectedDateEnd'");
foreach ($resourcesNotHereAfterEnd as $res) {
  foreach($resourcesToShow as $key=>$value) {
    if ($res == $value) {
      unset($resourcesToShow["$key"]);
    }
  }
}
//team
if($paramTeam){
  $resourcesTeam =SqlList::getListWithCrit('ResourceAll', array('idTeam'=>$paramTeam));
  $resourcesToShow = array_intersect($resourcesToShow,$resourcesTeam);
}

// organization
if($idOrganization){
  $orga = new Organization($idOrganization);
  $listResOrg=$orga->getResourcesOfAllSubOrganizationsListAsArray();
  foreach ($resourcesToShow as $idR=>$nameR){
    if(! in_array($idR, $listResOrg)) unset($resourcesToShow[$idR]);
  }
}
foreach ($lstWork as $work) {
  //if (! isset($resourcesToShow[$work->idResource])) continue;
  if ($paramProject and isset($listProj) and !isset($listProj[$work->idProject]) ) continue;
  if (! array_key_exists($work->idResource,$resources)) {
    if ($paramTeam) {
      $team=SqlList::getFieldFromId('ResourceAll', $work->idResource,'idTeam');
      if ($team!=$paramTeam) continue;
    }
    if ($idOrganization) {
      $orga=SqlList::getFieldFromId('ResourceAll', $work->idResource,'idOrganization');
      if ($orga!=$idOrganization) continue;
    }
	$nameResToAdd=SqlList::getNameFromId('ResourceAll', $work->idResource);
    if ($nameResToAdd==$work->idResource) continue;
    $resources[$work->idResource]=$nameResToAdd;
  	$resourceCapacity[$work->idResource]=SqlList::getFieldFromId('ResourceAll', $work->idResource, 'capacity');
    $result[$work->idResource]=array();
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
    $proj=new Project($work->idProject);
    $projectsColor[$work->idProject]=$proj->getColor();
  }
  if (! array_key_exists($work->day,$result[$work->idResource])) {
    $result[$work->idResource][$work->day]=array();
  }
  if (! array_key_exists($work->idProject,$result[$work->idResource][$work->day])) {
    $result[$work->idResource][$work->day][$work->idProject]=0;
    $result[$work->idResource][$work->day]['real']=true;
  }
  if (!array_key_exists('refType', $result[$work->idResource][$work->day])) {
      $result[$work->idResource][$work->day]['refType'] = $work->refType;
  }else {
    $result[$work->idResource][$work->day]['refType'] = '';
  }
  if (!array_key_exists('refId', $result[$work->idResource][$work->day])) {
      $result[$work->idResource][$work->day]['refId'] = $work->refId;
  }else {
    $result[$work->idResource][$work->day]['refId'] = '';
  }
  $result[$work->idResource][$work->day][$work->idProject]+=$work->work;
  //echo "work : " . htmlEncode($work->day) . " / " . htmlEncode($work->idProject) . " / " . htmlEncode($work->idResource) . " / " . htmlEncode($work->work) . "<br/>";
}
$planWork=new PlannedWork();
$lstPlanWork=$planWork->getSqlElementsFromCriteria(null,false, $where, $order);
foreach ($lstPlanWork as $work) {  
  //if (! isset($resourcesToShow[$work->idResource])) continue;
  if ($paramProject and isset($listProj) and !isset($listProj[$work->idProject]) ) continue;
  if (! array_key_exists($work->idResource,$resources)) {
    if ($paramTeam) {
      $team=SqlList::getFieldFromId('ResourceAll', $work->idResource,'idTeam');
      if ($team!=$paramTeam) continue;
    }
    if ($idOrganization) {
      $orga=SqlList::getFieldFromId('ResourceAll', $work->idResource,'idOrganization');
      if ($orga!=$idOrganization) continue;
    }
    
    $nameResToAdd=SqlList::getNameFromId('ResourceAll', $work->idResource);
    if ($nameResToAdd==$work->idResource) continue;
    $resources[$work->idResource]=$nameResToAdd;
    $resourceCapacity[$work->idResource]=SqlList::getFieldFromId('ResourceAll', $work->idResource, 'capacity');
    $result[$work->idResource]=array();
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
    $proj=new Project($work->idProject);
    $projectsColor[$work->idProject]=$proj->getColor();
  }
  if (! array_key_exists($work->day,$result[$work->idResource])) {
    $result[$work->idResource][$work->day]=array();
  }
  if (! array_key_exists($work->idProject,$result[$work->idResource][$work->day])) {
    $result[$work->idResource][$work->day][$work->idProject]=0;
  }
  if (! array_key_exists('real',$result[$work->idResource][$work->day]) or $work->workDate>=date('Y-m-d')) { // Do not add planned if real exists 
    // PBER : show planned in the future event if real exists (may be admin project, or planned intervention of booked in advance)
    $result[$work->idResource][$work->day][$work->idProject]+=$work->work;
  }
  if (!array_key_exists('refType', $result[$work->idResource][$work->day])) {
      $result[$work->idResource][$work->day]['refType'] = $work->refType;
  }else {
    $result[$work->idResource][$work->day]['refType'] = '';
  }
  if (!array_key_exists('refId', $result[$work->idResource][$work->day])) {
      $result[$work->idResource][$work->day]['refId'] = $work->refId;
  }else {
    $result[$work->idResource][$work->day]['refId'] = '';
  }
}

if ($periodType=='month') {
  $startDate=$periodValue. "01";
  if (!$paramYear) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('year'))); // TODO i18n message
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  } 
  $time=mktime(0, 0, 0, $paramMonth, 1, $paramYear);
  $header=i18n(pq_strftime("%B", $time))." ".pq_strftime("%Y", $time);
  $nbDays=date("t", $time);
}
$weekendBGColor='#cfcfcf';
$weekendFrontColor='#555555';
$weekendStyle=' style="background-color:' . $weekendBGColor . '; color:' . $weekendFrontColor . '" ';

$resourcesToShow=array_merge_preserve_keys($resourcesToShow,$resources);
$month=$paramYear.'-'.$paramMonth;
if (count($resourcesToShow)==0 and checkNoData($result,$month)) continue;
echo '<table style="width:95%;align:center;" id="areaColorPlanDetail" >';
echo '<tr><td>';
echo '<table width="100%" align="left">';
echo '<tr>';
echo '<td class="reportTableDataFull">';
echo '<div style="height:20px;width:20px;position:relative;background-color:#DDDDDD;">&nbsp;';
echo '<div style="width:20px;position:absolute;top:3px;left:5px;color:#000000;">R</div>';
echo '<div style="width:20px;position:absolute;top:2px;left:6px;color:#FFFFFF;">R</div>';
echo '</div>';
echo '</td><td style="width:100px; padding-left:5px;" class="legend">' . i18n('colRealWork') . '</td>';
echo '<td style="width:5px";>&nbsp;&nbsp;&nbsp;</td>';
echo '<td class="reportTableDataFull">';
echo '<div style="height:20px;width:20px;position:relative;background-color:#DDDDDD;">&nbsp;';
echo '</div>';
echo '</td><td style="width;100px; padding-left:5px;" class="legend">' . i18n('colPlanned') . '</td>';
echo '<td>&nbsp;</td>';
echo "</tr></table>";
//echo "<br/>";

echo '<table width="100%" align="left"><tr>';
$sortProject=array();
foreach ($projects as $id=>$name) {
  $sortProject[SqlList::getFieldFromId('Project', $id, 'sortOrder').'#'.$id]=$name;
}
ksort($sortProject);
$projects=array();
foreach ($sortProject as $sortId=>$name) {
  $split=pq_explode('#', $sortId);
  $projects[$split[1]]=$name;
}
$cptProj=0;
foreach($projects as $idP=>$nameP) {
	if ((($cptProj) % 8)==0) { echo '</tr><tr>';}
	$cptProj++;
  echo '<td width="20px">';
  echo '<div style="border:1px solid #AAAAAA ;height:20px;width:20px;position:relative;background-color:' . (($projectsColor[$idP])?$projectsColor[$idP]:"#FFFFFF") . ';">&nbsp;';
  echo '</div>';
  echo '</td><td style="width:100px; padding-left:5px;" class="legend">' . htmlEncode($nameP) . '</td>';
  echo '<td width="5px">&nbsp;&nbsp;&nbsp;</td>';
}

echo '<td>&nbsp;</td></tr></table>';
//echo '<br/>';
// title
echo '<table align="center"><tr><td class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
echo '<td colspan="' . $nbDays . '" class="reportTableHeader">' . $header . '</td>';
echo '</tr><tr>';
$days=array();
$daysCal=array();
$lstCal=SqlList::getList('CalendarDefinition');
foreach($lstCal as $idCal=>$nameCal) {$daysCal[$idCal]=array();}
for($i=1; $i<=$nbDays;$i++) {
  if ($periodType=='month') {
    $day=(($i<10)?'0':'') . $i;
    if (isOffDay(pq_substr($periodValue,0,4) . "-" . pq_substr($periodValue,4,2) . "-" . $day)) {
      $days[$periodValue . $day]="off";
      $style=$weekendStyle;
    } else {
      $days[$periodValue . $day]="open";
      $style='';
    }
    foreach($lstCal as $idCal=>$nameCal) {
      if (isOffDay(pq_substr($periodValue,0,4) . "-" . pq_substr($periodValue,4,2) . "-" . $day,$idCal)) {
        $daysCal[$idCal][$periodValue . $day]="off";
      } else {
        $daysCal[$idCal][$periodValue . $day]="open";
      }
    }
    echo '<td class="reportTableColumnHeader" ' . $style . '>' . $day . '</td>';
  }  
}

echo '</tr>';

asort($resourcesToShow);

foreach ($resourcesToShow as $idR=>$nameR) {
  $idCal=SqlList::getFieldFromId('Affectable', $idR, 'idCalendarDefinition');
	//if ($paramTeam) {
    $res=new ResourceAll($idR);//florent ticket #5038
  //}
  if (!$paramTeam or $res->idTeam==$paramTeam) {
    //gautier
    if(array_key_exists($idR,$resourceCapacity)){
  	  $capacity=$resourceCapacity[$idR];
    }else{
      $capacity=SqlList::getFieldFromId('ResourceAll', $idR, 'capacity');
    }
    $maxCapa = 0;
    for ($i=1; $i<=$nbDays;$i++) {
    	$day=$startDate+$i-1;
    	$weekDate = pq_substr($day, 0,4).'-'.pq_substr($day, 4, -2).'-'.pq_substr($day, 6);
    	if($res->getCapacityPeriod($weekDate) > $maxCapa){
    		$maxCapa = round($res->getCapacityPeriod($weekDate), 2);
    	}
    }
	  //echo '<tr height="20px"><td class="reportTableLineHeader" style="width:200px">' . $nameR;
    $trHeight =($scale=='same')?(20 * $maxCapa):20;
    echo '<tr height="' . $trHeight . 'px"><td class="reportTableLineHeader" style="width:200px">' . $nameR;
    
	  echo '<div style="float:right;font-size:80%;color:#A0A0A0;">';
	  if($capacity != $maxCapa){
	  	echo '<table width="100%"><tr><td style="width:50%;text-align:right;padding-right:10px;">'.htmlDisplayNumericWithoutTrailingZeros($capacity).'</td>';
	  	echo '<td style="width:50%;text-align:left;font-style:italic;">max('.$maxCapa.')</td></tr></table>';
	  }else{
	  	echo htmlDisplayNumericWithoutTrailingZeros($capacity);
	  }
	  echo '</div>';
	  echo '</td>';
	  for ($i=1; $i<=$nbDays;$i++) {
	    $day=$startDate+$i-1;
	    $style="";
	    //if ($days[$day]=="off") {
	    if ($idCal and $daysCal[$idCal][$day]=="off") {
	      $style=$weekendStyle;
	    }
	    echo '<td class="reportTableDataFull" ' . $style . ' valign="top" oncontextmenu="return false;">';
	    // test day and result
	    //if (array_key_exists($resources[$idR],$result) and array_key_exists($resources[$idR],$days )){
  	  if(isset($result[$idR])){
  	    if (array_key_exists($day,$result[$idR])) {
  	      echo "<div style='position:relative;'>";
  	      $real=false;
  	      foreach ($result[$idR][$day] as $idP=>$val) {
  	        if ($idP=='real') {
  	          $real=true;
  	        }else if ($idP == 'refType'){ 
        	      $refTypeCell = $val;
  	        }else if ($idP == 'refId'){ 
        	      $refIdCell = $val; 
  	        }
  	      }
  	      foreach ($result[$idR][$day] as $idP=>$val) {
  	        if (!in_array($idP, ['real', 'refType', 'refId'])) {
  	          if($capacity != 0){
  	            //$height=floor(20*$val/$capacity);
  	            if (!$maxCapa) $maxCapa=1;
  	            if (!$capacity) $capacit=1;
  	            if ($scale=='same') $height=floor($trHeight*$val/$maxCapa);
  	            else $height=floor($trHeight*$val/$capacity); 	            
  	            $dateCell=pq_substr($day,0,4).'-'.pq_substr($day,4,2).'-'.pq_substr($day,6,2);
  	            $idcolorPlanDetailDiv = "colorPlanDetailDiv-" . $idR . "-" . $dateCell;
  	            $varOnclickOnContextMenu = !$printPreview ? " onclick='reportGotoElement(\"$idR\", \"$dateCell\", \"$refTypeCell\", \"$refIdCell\", \"$idP\", event, \"" . htmlEncode($projects[$idP],'parameter') . "\");' oncontextmenu=\"reportExtraInformations('$idR', '$dateCell', event); return false;\"" : '';
                  echo "<div  class='$idcolorPlanDetailDiv' id='colorPlanDetail' $varOnclickOnContextMenu style='cursor:pointer;position:relative;height:" . $height . "px; background-color:" . $projectsColor[$idP] . ";'></div>";
                  //$adjustedHeight = 20 - $height;
                  $adjustedHeight = $trHeight - $height;
                  if ($adjustedHeight  != 0 and !$real and $refTypeCell!=''){
                    echo "<div  class='$idcolorPlanDetailDiv' id='colorPlanDetail' $varOnclickOnContextMenu style='cursor:pointer;position:relative;height:" . $adjustedHeight . "px;'></div>";
                  }  
  	          }
  	        }
  	      }
  	      
  	      if ($real) {
  	        echo "<div style='user-select:none;pointer-events:none;position:absolute;top:3px;left:5px;color:#000000;'>R</div>";
  	        echo "<div style='user-select:none;pointer-events:none;position:absolute;top:2px;left:6px;color:#FFFFFF;'>R</div>";
  	      }
  	      
  	      echo "</div>";
  	    }
  	  }
	    echo '</td>';
	  }
	  echo '</tr>';
  }
}
echo '</table>';
echo '</td></tr></table>';
echo '<br/><br/>';
if($outMode !="pdf"){
  echo "<div id='colorPlanDetailDiv' style='z-index:998;display:none; position:fixed; background-color:#FFFFFF; padding:7px; xpadding-top:10px; border:1px solid var(--color-medium); border-radius: 5px 0px 5px 5px; width:600px; max-height:140px; overflow-x:hidden; overflow-y:auto;'></div>";
  echo "<div id='colorPlanClose' onclick='closeColorPlanDetails();' style='z-index:999;display:none; position:fixed; background-color:#FFFFFF; border:1px solid var(--color-medium); border-bottom:0;width:25px; height:20px; border-radius:5px 5px 0px 0px; justify-content: center; align-items: center;'> <div class='iconClose iconSize16 pointer'></div></div>";
}
// END OF LOOP ON MONTH
}
end:

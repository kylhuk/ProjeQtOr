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

//echo "expensePlan.php";
include_once '../tool/projeqtor.php';
$idProject="";
if (array_key_exists('idProject',$_REQUEST) and pq_trim($_REQUEST['idProject'])!="") {
  $idProject=pq_trim($_REQUEST['idProject']);
  $idProject = Security::checkValidId($idProject);
}
$idResource="";
if (array_key_exists('idResource',$_REQUEST) and pq_trim($_REQUEST['idResource'])!="") {
  $idResource=pq_trim($_REQUEST['idResource']);
  $idResource = Security::checkValidId($idResource); // only allow digits
}
$scale='month';
if (array_key_exists('scale',$_REQUEST)) {
  $scale=$_REQUEST['scale'];
  $scale=Security::checkValidPeriodScale($scale);
}
$scope='';
if (array_key_exists('scope',$_REQUEST)) {
  $scope=$_REQUEST['scope'];
  $scope=Security::checkValidAlphanumeric($scope);
}

$headerParameters="";
if ($idProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project',$idProject)) . '<br/>';
}
if ($idResource!="") {
  $headerParameters.= i18n("colIdResource") . ' : ' . htmlEncode(SqlList::getNameFromId('Resource',$idResource)) . '<br/>';
}

$showVAT='';
$colmumnRealName='realAmount';
$colmumnPlannedName='plannedAmount';
if (array_key_exists('showVAT',$_REQUEST)) {
  $showVAT=$_REQUEST['showVAT'];
  $showVAT=Security::checkValidAlphanumeric($showVAT);
  if ($showVAT=='on') {
     $colmumnRealName='realFullAmount';
     $colmumnPlannedName='plannedFullAmount';
  }
}

include "header.php";

$accessRightRead=securityGetAccessRight('menuProject', 'read');
  
$user=getSessionUser();
$queryWhere=getAccesRestrictionClause('ProjectExpense','exp',false,true,true);
//echo $queryWhere;

if ($idProject!='') {
  $queryWhere.=  " and exp.idProject in " . getVisibleProjectsList(true, $idProject) ;
} else {
  //
}
// Remove Admin Projects : should not appear in Work Plan
$queryWhere.= " and exp.idProject not in " . Project::getAdminitrativeProjectList() ;

$querySelect1= 'select sum(exp.'.$colmumnRealName.') as sumCost, exp.' . $scale . ' as scale , exp.idProject'; 
$queryGroupBy1 = 'exp.'.$scale . ', exp.idProject, t2.sortOrder';
$queryWhere1 = $queryWhere . ' and exp.expenseRealDate is not null ';

$querySelect2= 'select sum(exp.'.$colmumnPlannedName.') as sumCost, exp.' . $scale . ' as scale , exp.idProject'; 
$queryGroupBy2 = 'exp.'.$scale . ', exp.idProject, t2.sortOrder';
$queryWhere2 = $queryWhere . ' and exp.expenseRealDate is null ';

if ($scope) {
	$scopeWhere = " and scope='" . $scope . "Expense' ";
	$queryWhere1 .= $scopeWhere;
	$queryWhere2 .= $scopeWhere;
}
if ($idResource) {
	$resWhere = " and exp.idResource=" . $idResource . " ";
  $queryWhere1 .= $resWhere;
  $queryWhere2 .= $resWhere;
}
// constitute query and execute

$tab=array();
$start="";
$end="";
for ($i=1;$i<=2;$i++) {
  $obj=new ProjectExpense();
  $proj= new Project();
  $ass=new Assignment();
  $var=($i==1)?'real':'plan';
  $querySelect=($i==1)?$querySelect1:$querySelect2;
  $queryGroupBy=($i==1)?$queryGroupBy1:$queryGroupBy2;
  $queryWhere=($i==1)?$queryWhere1:$queryWhere2;
  //$queryFrom=($i==1)?$queryFrom1:$queryFrom2;
  $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
  $query=$querySelect 
     . ' from ' . $obj->getDatabaseTableName().' exp, '.$proj->getDatabaseTableName().' t2 ' 
     . ' where ' . $queryWhere.' AND t2.id=exp.idProject '
     . ' and exp.cancelled=0'  
     . ' group by ' . $queryGroupBy
     . ' order by t2.sortOrder asc '; 
  $result=Sql::query($query);
//echo $query . '<br/><br/>';
  
  while ($line = Sql::fetchLine($result)) {
  	$line=array_change_key_case($line,CASE_LOWER);
    $date=$line['scale'];
    $proj=$line['idproject'];
    $cost=round(pq_nvl($line['sumcost'],0),2);
    if (! isset($tab[$proj]) ) {
      $tab[$proj]=array("name"=>SqlList::getNameFromId('Project', $proj), "real"=>array(),"plan"=>array());
    }
    $tab[$proj][$var][$date]=$cost;
    if ( ! $start or ($start>$date and $date)) {
      $start=$date;
    }
    if (! $end or $end<$date) {
      $end=$date;
    }
  }
}
if (! $start and $end) {$start=$end;}
if (! $start and ! $end) {$tab=array();}
if (checkNoData($tab)) if (!empty($cronnedScript)) goto end; else exit;

$arrDates=array();
$arrYear=array();
$date=$start;

while ($date<=$end) {
  $arrDates[]=$date;
  $year=pq_substr($date,0,4);
  if (! array_key_exists($year,$arrYear)) {
    $arrYear[$year]=0;
  }
  $arrYear[$year]+=1;
  if ($scale=='week') {
    $day=date('Y-m-d',firstDayofWeek(pq_substr($date,4,2),pq_substr($date,0,4)));
    $next=addWeeksToDate($day,1);
    $date=pq_str_replace('-','', weekFormat($next));
  } else {
    $day=pq_substr($date,0,4) . '-' . pq_substr($date,4,2) . '-01';
    $next=addMonthsToDate($day,1);
    $date=pq_substr($next,0,4) . pq_substr($next,5,2);
  }
}
// Header
$plannedBGColor='#FFFFDD';
$plannedFrontColor='#777777';
$plannedStyle=' style="width:20px;text-align:center;background-color:' . $plannedBGColor . '; color: ' . $plannedFrontColor . ';" ';

echo "<table width='95%' align='center'><tr>";
echo '<td><table width="100%" align="left"><tr>';
echo "<td class='reportTableDataFull' style='width:20px; text-align:center;'>";
echo "1";
echo "</td><td width='100px' class='legend'>" . i18n('colRealCost') . "</td>";
echo "<td width='5px'>&nbsp;&nbsp;&nbsp;</td>";
echo '<td class="reportTableDataFull" ' . $plannedStyle . '>';
echo "<i>1</i>";
echo "</td><td width='100px' class='legend'>" . i18n('colPlanned') . "</td>";
echo "<td>&nbsp;</td>";
echo "</tr></table>";
echo "<br/>";
echo '<table width="100%" align="center">';
echo '<tr rowspan="2">';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Project') . '</td>';
foreach ($arrYear as $year=>$nb) {
  echo '<td class="reportTableHeader" colspan="' . $nb . '">' . $year . '</td>';
}
echo '<td class="reportTableHeader" rowspan="2">' . i18n('sum') . '</td>';
echo '</tr>';
echo '<tr>';
$arrSum=array();
foreach ($arrDates as $date) {
  echo '<td class="reportTableColumnHeader" >';
  echo pq_substr($date,4,2); 
  echo '</td>';
  $arrSum[$date]=0;
} 
echo '</tr>';
$sumProj=array();
foreach($tab as $proj=>$lists) {
  $sumProj[$proj]=array();
  for ($i=1; $i<=2; $i++) {
    if ($i==1) {
      echo '<tr><td class="reportTableLineHeader" rowspan="2">' . htmlEncode($lists['name']) . '</td>';
      $style='';
      $mode='real';
      $ital=false;
    } else {
      echo '<tr>';
      $style=$plannedStyle;
      $mode='plan';
      $ital=true;
    }
    $sum=0;
    foreach($arrDates as $date) {
      if ($i==1) {
        $sumProj[$proj][$date]=0;
      }
      $val=0;
      if (array_key_exists($mode, $lists) and array_key_exists($date,$lists[$mode])) {
        $val=$lists[$mode][$date];
      }
      echo '<td class="reportTableData" ' . $style . '>';
      echo ($ital)?'<i>':'';
      echo htmlDisplayCurrency($val);
      echo ($ital)?'</i>':'';
      $sum+=$val;
      $arrSum[$date]+=$val;
      echo '</td>';
      $sumProj[$proj][$date]+=$val;
    }
    echo '<td class="reportTableColumnHeader">';
    echo ($ital)?'<i>':'';
    echo htmlDisplayCurrency($sum);
    echo ($ital)?'</i>':'';
    echo '</td>';
    echo '</tr>';
    
  }
}
echo "<tr><td>&nbsp;</td></tr>";
echo '<tr><td class="reportTableHeader" >' . i18n('sum') . '</td>';
$sum=0;
$cumul=array();
foreach ($arrSum as $date=>$val) {
  echo '<td class="reportTableHeader" >' . htmlDisplayCurrency($val) . '</td>';
  $sum+=$val;
  $cumul[$date]=$sum;
}
echo '<td class="reportTableHeader" >' . htmlDisplayCurrency($sum) . '</td>';
echo '</tr>';
echo '</table>';
echo '</td></tr></table>';
// Graph
if (! testGraphEnabled()) { return;}
  include_once("../external/pChart2/class/pData.class.php");
  include_once("../external/pChart2/class/pDraw.class.php");
  include_once("../external/pChart2/class/pImage.class.php"); 
$dataSet=new pData;
$nbItem=0;
foreach($sumProj as $id=>$vals) {
  $proj = SqlList::getNameFromId('Project', $id);
  $dataSet->addPoints($vals,$proj);
  $dataSet->setSerieDescription($tab[$id]['name'],$proj);
  $dataSet->setSerieOnAxis($proj,0);
  $proje=new Project($id);
  $projCol = $proje->color;
  $projectColor=$proje->getColor();
  $colorProj=hex2rgb($projectColor);
  if($projCol){
    $serieSettings = array("R"=>$colorProj['R'],"G"=>$colorProj['G'],"B"=>$colorProj['B']);
    $dataSet->setPalette($proj,$serieSettings);
  } else {
    $serieSettings = array("R"=>$rgbPalette[($nbItem % 12)]['R'],"G"=>$rgbPalette[($nbItem % 12)]['G'],"B"=>$rgbPalette[($nbItem % 12)]['B']);
    $dataSet->setPalette($proj,$serieSettings);
  }
  $nbItem++;
}
$arrLabel=array();
foreach($arrDates as $date){
  $arrLabel[]=pq_substr($date,0,4) . '-' . pq_substr($date,4,2);
}
$dataSet->addPoints($arrLabel,"dates");
$dataSet->setAbscissa("dates");   
$dataSet->setSerieOnAxis("dates",0);

$width=1000;
$legendWidth=300;
$height=400;
$legendHeight=100;
$graph = new pImage($width+$legendWidth, $height,$dataSet);

/* Draw the background */
$graph->Antialias = FALSE;

/* Add a border to the picture */
$settings = array("R"=>240, "G"=>240, "B"=>240, "Dash"=>0, "DashR"=>0, "DashG"=>0, "DashB"=>0);
$graph->drawRoundedRectangle(5,5,$width+$legendWidth-8,$height-5,5,$settings);
$graph->drawRectangle(0,0,$width+$legendWidth-1,$height-1,array("R"=>150,"G"=>150,"B"=>150));

/* Set the default font */
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>8));

/* title */
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>8,"R"=>100,"G"=>100,"B"=>100));
$graph->drawLegend($width+30,17,array("Mode"=>LEGEND_VERTICAL, "Family"=>LEGEND_FAMILY_BOX ,
    "R"=>255,"G"=>255,"B"=>255,"Alpha"=>100,
    "FontR"=>55,"FontG"=>55,"FontB"=>55,
    "Margin"=>5));

/* Draw the scale */
$graph->setGraphArea(60,50,$width-20,$height-$legendHeight);
$formatGrid=array("Mode"=>SCALE_MODE_ADDALL_START0, "GridTicks"=>0,
    "DrawYLines"=>array(0), "DrawXLines"=>false,"Pos"=>SCALE_POS_LEFTRIGHT,
    "LabelRotation"=>90, "GridR"=>200,"GridG"=>200,"GridB"=>200);
$graph->drawScale($formatGrid);
$graph->Antialias = TRUE;
$graph->drawStackedBarChart();


foreach($sumProj as $id=>$vals) {
  $dataSet->RemoveSerie($tab[$id]['name']);
}

$dataSet->setAxisPosition(0,AXIS_POSITION_RIGHT);
$dataSet->addPoints($cumul,"sum");
$dataSet->setSerieDescription(i18n("cumulated"),"sum");
$dataSet->setSerieOnAxis("sum",0);
$dataSet->setAxisName(0,i18n("cumulated"));

$formatGrid=array("LabelRotation"=>90,"DrawXLines"=>FALSE,"DrawYLines"=>NONE);
$graph->drawScale($formatGrid);

$graph->drawLineChart();
$graph->drawPlotChart();

$imgName=getGraphImgName("globalCostPlanning");
$graph->render($imgName);
echo '<table width="95%" style="margin-top:20px;" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />'; 
echo '</td></tr></table>';
echo '<br/>';

end:

?>
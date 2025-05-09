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

include_once '../tool/projeqtor.php';
include_once '../tool/formatter.php';
//echo 'versionReport.php';

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=pq_trim($_REQUEST['idProject']);
  $paramProject=Security::checkValidId($paramProject); // only allow digits
};
  
$paramProduct='';
if (array_key_exists('idProduct',$_REQUEST)) {
  $paramProduct=pq_trim($_REQUEST['idProduct']);
  $paramProduct=Security::checkValidId($paramProduct); // only allow digits
};
$paramVersion='';
if (array_key_exists('idVersion',$_REQUEST)) {
  $paramVersion=pq_trim($_REQUEST['idVersion']);
  $paramVersion=Security::checkValidId($paramVersion); // only allow digits
};
$paramDetail=false;
if (array_key_exists('showDetail',$_REQUEST)) {
  $paramDetail=pq_trim($_REQUEST['showDetail']); // no need to filter as only used in comparison.
}

$user=getSessionUser();
  
  // Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($paramProduct!="") {
  $headerParameters.= i18n("colIdProduct") . ' : ' . htmlEncode(SqlList::getNameFromId('Product', $paramProduct)) . '<br/>';
}
if ($paramVersion!="") {
  $headerParameters.= i18n("colVersion") . ' : ' . htmlEncode(SqlList::getNameFromId('Version', $paramVersion)) . '<br/>';
}
include "header.php";

$where=getAccesRestrictionClause('Requirement',false);

$order="";

if ($paramProject) {
  $lstProject=array($paramProject=>SqlList::getNameFromId('Project',$paramProject));
  $where.=" and idProject=".Sql::fmtId($paramProject);
} else {
  $lstProject=SqlList::getList('Project','name',null,true);
  $lstProject[0]='<i>'.i18n('undefinedValue').'</i>';
}

if ($paramProduct) {
  $lstProduct=array($paramProduct=>SqlList::getNameFromId('Product',$paramProduct));
  $where.=" and idProduct=".Sql::fmtId($paramProduct);
} else {
  $lstProduct=SqlList::getList('Product','name',null,true);
  $lstProduct[0]='<i>'.i18n('undefinedValue').'</i>';
}

if ($paramVersion) {
  $lstVersion=array($paramVersion=>SqlList::getNameFromId('Version',$paramVersion));
  $where.=" and idTargetVersion=".Sql::fmtId($paramVersion);
} else {
  $lstVersion=SqlList::getList('Version','name',null,true);
  $lstVersion[0]='<i>'.i18n('undefinedValue').'</i>';
}

$lstType=SqlList::getList('RequirementType','name',null,true);

$req=new Requirement();
$lst=$req->getSqlElementsFromCriteria(null, false, $where,'idProject, idProduct, idVersion, id');

if (checkNoData($lst)) if (!empty($cronnedScript)) goto end; else exit;

echo '<table style="width:' . ((isset($outMode) and $outMode=='pdf')?'90':'95') . '%" align="center" '.excelName().'>';
echo '<tr>';
echo '<td class="reportTableHeader" style="width:8%" rowspan="2" '.excelFormatCell('header',30).'>' . i18n('colIdProject') . '</td>';
echo '<td class="reportTableHeader" style="width:8%" rowspan="2" '.excelFormatCell('header',30).'>' . i18n('colIdProduct') . '</td>';
echo '<td class="reportTableHeader" style="width:12%" rowspan="2" '.excelFormatCell('header',30).' >' . i18n('colIdVersion') . '</td>';
echo '<td class="reportTableHeader" style="width:8%" rowspan="2" '.excelFormatCell('header',20).' >' . i18n('colType') . '</td>';
//echo '<td class="reportTableHeader" style="width:4%" rowspan="2" >' . i18n('colId') . '</td>';
//echo '<td class="reportTableHeader" style="width:12%" rowpan="2" >' . i18n('colReference') . '</td>';
echo '<td class="reportTableHeader" style="width:40%" colspan="2" rowspan="2" '.excelFormatCell('header',80).' >' . i18n('Requirement') . '</td>';
echo '<td class="reportTableHeader" style="width:25%" colspan="5" '.excelFormatCell('header',50).' >' .  i18n('TestCase') . " / " . i18n('sectionProgress') . '</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;" '.excelFormatCell('header',10).'>' . i18n('colCountLinked') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;" '.excelFormatCell('header',10).'>' . i18n('colCountPlanned') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;" '.excelFormatCell('header',10).'>' . i18n('colCountPassed') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;" '.excelFormatCell('header',10).'>' . i18n('colCountBlocked') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;" '.excelFormatCell('header',10).'>' . i18n('colCountFailed') . '</td>';
echo '</tr>';
$sumPlanned=0;
$sumLinked=0;
$sumPassed=0;
$sumBlocked=0;
$sumFailed=0;
$cpt=0;
$sumReal='';
  
if ($paramDetail) {
  echo '<tr><td colspan="10" style="font-size:3px;">&nbsp;</td></tr>';
}
foreach ($lst as $req) {
 if ($req->idRequirementType and ! isset($lstType[$req->idRequirementType])) {
    $rtype=new RequirementType($req->idRequirementType);
    $lstType[$req->idRequirementType]=$rtype->name;
  }
  echo '<tr>';
  echo '<td class="reportTableData" style="text-align:left;width:8%" '.excelFormatCell('data',null,null,null,null,'left').' >' . (($req->idProject)?$lstProject[$req->idProject]:'') . '</td>';
  echo '<td class="reportTableData" style="text-align:left;width:8%" '.excelFormatCell('data',null,null,null,null,'left').' >' . (($req->idProduct)?$lstProduct[$req->idProduct]:'') . '</td>';
  echo '<td class="reportTableData" style="text-align:left;width:12%" '.excelFormatCell('data',null,null,null,null,'left').' >' . (($req->idTargetProductVersion)?$lstVersion[$req->idTargetProductVersion]:'') . '</td>';
  echo '<td class="reportTableData" style="width:8%" '.excelFormatCell('data').' >' . (($req->idRequirementType)?$lstType[$req->idRequirementType]:'') . '</td>';
  echo '<td class="reportTableData" style="width:5%" '.excelFormatCell('data',10).' >#' . htmlEncode($req->id) . '</td>';
  echo '<td class="reportTableData" style="text-align:left;width:35%;" '.excelFormatCell('data',70,null,null,null,'left').' >' . htmlEncode($req->name) . '</td>';
  echo '<td class="reportTableData" style="width:5%" '.excelFormatCell('data').' >' . htmlEncode($req->countLinked) . '</td>';
  echo '<td class="reportTableData" style="width:5%;" '.excelFormatCell('data').' >' . htmlEncode($req->countPlanned) . '</td>';
  echo '<td class="reportTableData" style="width:5%; ' . (($req->countPassed and $req->countPassed==$req->countPlanned)?'color:green;':'') . '" '.excelFormatCell('data').'>' . htmlEncode($req->countPassed) . '</td>';
  echo '<td class="reportTableData" style="width:5%; ' . (($req->countBlocked)?'color:orange;':'') . '" '.excelFormatCell('data').'>' . htmlEncode($req->countBlocked) . '</td>';
  echo '<td class="reportTableData" style="width:5%; ' . (($req->countFailed)?'color:red;':'') . '" '.excelFormatCell('data').'>' . htmlEncode($req->countFailed) . '</td>';
  echo '</tr>';
  $sumLinked+=$req->countLinked;
  $sumPlanned+=$req->countPlanned;
  $sumPassed+=$req->countPassed;
  $sumBlocked+=$req->countBlocked;
  $sumFailed+=$req->countFailed;
  $cpt+=1;
  if ($paramDetail) {
  	$link=new Link();
  	$crit=array('ref1Type'=>'Requirement', 'ref1Id'=>$req->id, 'ref2Type'=>'TestCase');
  	$lst=$link->getSqlElementsFromCriteria($crit, null, null, 'ref2id');
  	if (count($lst)>0) {
  	  if($outMode!='excel'){
	  	  echo '<tr><td style="width:8%"   ></td><td style="width:92%" colspan="10"   >';
	  	  echo '<table style="width:100%" >';
  	  }
	  	echo '<tr>';
	  	if($outMode!='excel'){
	  	  echo '<td class="largeReportHeader" colspan="2" style="width:45%" '.excelFormatCell('header').'>' . i18n('TestCase') . '</td>';
	  	  echo '<td class="largeReportHeader" colspan="2" style="width:40%" '.excelFormatCell('header').'>' . i18n('TestSession') . '</td>';
	  	  echo '<td class="largeReportHeader" colspan="2" style="width:15%" '.excelFormatCell('header').'>' . i18n('colResult') . '</td>';
	  	}else{
	  	  echo '<td class="largeReportHeader" '.excelFormatCell('subheader').'></td>';
	  	  echo '<td class="largeReportHeader" colspan="3" '.excelFormatCell('subheader').'>' . i18n('TestCase') . '</td>';
	  	  echo '<td class="largeReportHeader" colspan="3" '.excelFormatCell('subheader').'>' . i18n('TestSession') . '</td>';
	  	  echo '<td class="largeReportHeader" colspan="4" '.excelFormatCell('subheader').'>' . i18n('colResult') . '</td>';
	  	  //echo '<td class="largeReportHeader" colspan="4"></td>';
	  	}

	  	echo '</tr>';
	  	
  	  foreach ($lst as $link) {
        $tcr=new TestCaseRun();
        $crit=array('idTestCase'=>$link->ref2Id);
        $lstTcr=$tcr->getSqlElementsFromCriteria($crit,true, false, 'idTestSession');
        foreach ($lstTcr as $tcr) {
        	echo '<tr>';
        	$st=new RunStatus($tcr->idRunStatus);
        	if($outMode=='excel'){
        	  echo '<td class="largeReportHeader" '.excelFormatCell('data').'></td>';
        	  echo '<td class="largeReportHeader" colspan="3" '.excelFormatCell('data',null,null,null,null,'left').'>#' . htmlEncode($tcr->idTestCase) ."&nbsp;&nbsp;". SqlList::getNameFromId('TestCase',$tcr->idTestCase) . '</td>';
        	  echo '<td class="largeReportHeader" '.excelFormatCell('data').'>' . (($tcr->idTestSession)?'#':'') . $tcr->idTestSession . '</td>';
        	  echo '<td class="largeReportHeader" colspan="2" '.excelFormatCell('data',null,null,null,null,'left').'>' . (($tcr->idTestSession)?SqlList::getNameFromId('TestSession', $tcr->idTestSession):'') . '</td>';
        	  echo '<td class="largeReportHeader" colspan="2"'.excelFormatCell('data',null,null,$st->color).'>' . i18n($st->name). '</td>';
        	  echo '<td class="largeReportHeader" colspan="2"'.excelFormatCell('data',null).'>' . htmlFormatDate($tcr->statusDateTime, true) . '</td>';
        	  //echo '<td class="largeReportHeader" colspan="4"></td>';
        	}else{
          	  echo '<td class="largeReportData" style="width:5%" style="text-align: center;"  >#' . htmlEncode($tcr->idTestCase) . '</td>';
          	  echo '<td class="largeReportData style="width:40%"" >' . SqlList::getNameFromId('TestCase',$tcr->idTestCase) . '</td>';
          	  echo '<td class="largeReportData" style="width:5%" style="text-align: center;" >' . (($tcr->idTestSession)?'#':'') . $tcr->idTestSession . '</td>';
          	  echo '<td class="largeReportData" style="width:35%" >' . (($tcr->idTestSession)?SqlList::getNameFromId('TestSession', $tcr->idTestSession):'') . '</td>';
          	  echo '<td class="largeReportData" style="text-align: left;width:7%" >' . (($tcr->id)?colorNameFormatter(i18n($st->name) . '#split#' . $st->color):'') . '</td>';
          	  echo '<td class="largeReportData" style="text-align: center;font-size:75%;width:8%" >' . htmlFormatDate($tcr->statusDateTime, true) . '</td>';
        	}
            echo '</tr>';
        }
      }

      if($outMode!='excel'){
        echo '<tr><td colspan="7" style="font-size:3px;" style="width:100%"  '.excelFormatCell('data').' >&nbsp;</td></tr>';
        echo '</table>';
        echo '</td></tr>';
      }else{
        echo '<tr><td colspan="10"></td></tr>';
      }
     
  	}
  }
}
echo '<tr>';
echo '<td colspan="6"></td>';
echo '<td class="largeReportHeader" '.excelFormatCell('header').' >' . $sumLinked . '</td>';
echo '<td class="largeReportHeader" '.excelFormatCell('header').' >' . $sumPlanned . '</td>';
echo '<td class="largeReportHeader" '.excelFormatCell('header').'>' . $sumPassed . '</td>';
echo '<td class="largeReportHeader" '.excelFormatCell('header').'>' . $sumBlocked . '</td>';
echo '<td class="largeReportHeader" '.excelFormatCell('header').'>' . $sumFailed . '</td>';
echo '</tr>';
echo '</table>';
echo '<br/>';

end:

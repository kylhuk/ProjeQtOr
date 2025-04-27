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

/** ===========================================================================
 * Get the list of objects, in Json format, to display the grid list
 */
require_once "../tool/projeqtor.php";
//include_once('../tool/formatter.php');

$idResource=RequestHandler::getId('idResource');
$date=RequestHandler::getDatetime('date');

$result=array();
$w=new Work();
$p=new PlannedWork();
$crit=array('idResource'=>$idResource,'workDate'=>$date);
foreach (array($p,$w) as $work) {
  $wList=$work->getSqlElementsFromCriteria($crit);
  foreach($wList as $wk) {
    $idP=$wk->idProject;
    $refType=$wk->refType;
    $refId=$wk->refId;
    $key=$refType.'#'.$refId;
    $val=$wk->work;
    if (!isset($result[$idP])) $result[$idP]=array();
    if (!isset($result[$idP][$key])) $result[$idP][$key]=array('type'=>'P','value'=>0);
    $result[$idP][$key]['value']+=$val;
    if (get_class($work)=='Work') $result[$idP][$key]['type']='R';
  }
}
$clsP='Project';
echo '<table style="width:100%;vertical-align: top;">';
$caseName=SqlList::getNameFromId("Affectable",$idResource).' - '.htmlFormatDate($date,true,false);
echo '<tr class="simpleReportHeader"><td colspan="5">'.$caseName.'</td><td>'.i18n('colWbs').'</td><td style="text-align:center; width:100px;">'.i18n('colWork').'<div style="position:absolute;font-size:65%;font-weight:normal;width:100px"><span style="font-style:italic;">'.i18n('colPlanned').'</span> | <span style="font-weight:bold">'.i18n('colReal').'</span></div></td><td>&nbsp</td><tr>';
foreach($result as $idP=>$listP) {
  $nameP=SqlList::getNameFromId('Project',$idP);
  echo '<tr style="height:25px;vertical-align:top;" class="classLinkName" onClick="reportGotoElement(\''.$idResource.'\',\''.$date.'\',\''.$clsP.'\',\''.$idP.'\',\''.$idP.'\', event,\''.htmlEncode($nameP,'protectQuotes'). '\');">';
  echo '<td style="width:16px;vertical-align:top;">'.formatIcon($clsP, 16).'</td>';
  echo '<td style="width:16px;padding-right:10px;vertical-align:top;">&nbsp#' . $idP.'</td>';
  echo '<td style="vertical-align:top;" colspan="5">'.SqlList::getNameFromId($clsP, $idP) . '</td>';
  $colorP=SqlList::getFieldFromId("Project", $idP,"color");
  echo '<td style="width:16px vertical-align:top;"><div style="background:'.$colorP.'; margin-top:4px;margin-right:5px;width:25px;height:15px;float:right;border-radius:5px;border: 1px solid #AAAAAA;" align="center">&nbsp;</div></td>';
  echo '</tr>';
  foreach ($listP as $ref=>$val) {
    $exp=explode('#',$ref);
    $cls=$exp[0];
    $id=$exp[1];
    $refName=SqlList::getNameFromId($cls,$id);
    $refWbs = ($result = SqlList::getListWithCrit('PlanningElement', array('refName' => $refName), 'wbs')) ? array_values($result)[0] : '';
    echo '<tr style="height:25px"  class="classLinkName" onClick="reportGotoElement(\''.$idResource.'\',\''.$date.'\',\''.$cls.'\',\''.$id.'\',\''.$idP.'\', event,\''.htmlEncode($nameP,'protectQuotes'). '\');" >';
    echo '<td colspan="2">&nbsp;</td>';
    echo '<td style="width:16px;vertical-align:top;">' .formatIcon($cls, 16). '</td>';
    echo '<td style="width:16px;padding-right:10px;vertical-align:top;">&nbsp#'.$id.'</td>';
    echo '<td style="vertical-align:top;">'. SqlList::getNameFromId($cls, $id).'</td>';
    echo '<td style="vertical-align:top;">'.$refWbs.'</td>';
    $wk=Work::displayWorkWithUnit($val['value'],2);
    $wkType=($val['type']=='R')?'font-weight:bold;':'font-style:italic';
    echo '<td style="vertical-align:top;text-align:center;'.$wkType.'">'.$wk.'</td>';
    echo '</tr>';
  }
}
echo '</table>';
?>
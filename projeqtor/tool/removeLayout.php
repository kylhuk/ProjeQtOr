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
 * Save a layout : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

$comboDetail=false;
if (RequestHandler::isCodeSet('comboDetail')) {
  $comboDetail=true;
}

// Get the layout info
if (! array_key_exists('layoutObjectClass',$_REQUEST)) {
  throwError('layoutObjectClass parameter not found in REQUEST');
}
$layoutObjectClass=RequestHandler::getValue('layoutObjectClass');

if (! array_key_exists('idLayout',$_REQUEST)) {
  throwError('idLayout parameter not found in REQUEST');
}

if (!$comboDetail and array_key_exists($layoutObjectClass,$user->_arrayLayouts)) {
  $layoutArray=$user->_arrayLayouts[$layoutObjectClass];
} else {
  $layoutArray=array();
}
$currentLayout="";
if (! $comboDetail and array_key_exists($layoutObjectClass,$user->_arrayLayouts)) {
  $currentLayout=$user->_arrayLayouts[$layoutObjectClass]['id'];
}

$idLayout=$_REQUEST['idLayout']; // validated to be numeric value in SqlElement base constructor.
Sql::beginTransaction();
$layout=new Layout($idLayout);
$name=$layout->scope;
$result=$layout->delete();

$status=getLastOperationStatus($result);
if ($status=="OK"){
  $crit=array("idLayout"=>$layout->id ,"objectClass"=>$layout->objectClass, "idUser"=>$user->id, 'isReportList'=>'0');
  $layoutColumnSelector=new LayoutColumnSelector();
  $layoutColumnSelectorList = $layoutColumnSelector->getSqlElementsFromCriteria($crit);
  foreach ($layoutColumnSelectorList as $lColSelector){
    $lColSelector->delete();
  }
}
$lyt=new Layout();
$crit=array('idUser'=> $user->id, 'objectClass'=>$layoutObjectClass);
$orderByLayout = "sortOrder ASC";
$layoutList=$lyt->getSqlElementsFromCriteria($crit,false,null,$orderByLayout);
htmlDisplayStoredLayout($layoutList,$layoutObjectClass, $currentLayout);
echo "<div id='saveLayoutResult' align='center' style='z-index:9;position: absolute;left:50%;width:100%;margin-left:-50%;top:20px' >";
displayLastOperationStatus($result);
echo "</div>";
?>
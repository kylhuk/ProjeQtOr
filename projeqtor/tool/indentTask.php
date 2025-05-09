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
 * Move task (from before to)
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/indentTask.php');

$objectClass=RequestHandler::getClass('objectClass',true);
$objectId=RequestHandler::getId('objectId',true); 
$way=RequestHandler::getAlphanumeric('way',true); 
if ($way!='increase' and $way!='decrease') {
  $way='increase';
}

$result="";
Sql::beginTransaction();
$pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', array('refType'=>$objectClass,'refId'=>$objectId));
if ($pe and $pe->id) {
	$result=$pe->indent($way);
} else {
	$result=i18n('moveCancelled');
	$result .= '<input type="hidden" id="lastOperation" value="move" />';
	$result .= '<input type="hidden" id="lastOperationStatus" value="ERROR" />';
	$result .= '<input type="hidden" id="lastPlanStatus" value="KO" />';
}

displayLastOperationStatus($result);
?>
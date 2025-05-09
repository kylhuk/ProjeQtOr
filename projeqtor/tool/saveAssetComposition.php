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
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";


$idParent = RequestHandler::getId('idParent');
$listId = RequestHandler::getValue('assetStuctureListId');

$arrayId=array();
if (is_array($listId)) {
	$arrayId=$listId;
} else {
	$arrayId[]=$listId;
}
Sql::beginTransaction();
$result="";
// get the modifications (from request)
foreach ($arrayId as $id) {
	$str=new Asset($id);
	$str->idAsset=$idParent;
  $res=$str->save();
  if (!$result) {
    $result=$res;
  } else if (pq_stripos($res,'id="lastOperationStatus" value="OK"')>0 ) {
  	if (pq_stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
  		$deb=pq_stripos($res,'#');
  		$fin=pq_stripos($res,' ',$deb);
  		$resId=pq_substr($res,$deb, $fin-$deb);
  		$deb=pq_stripos($result,'#');
      $fin=pq_stripos($result,' ',$deb);
      $result=pq_substr($result, 0, $fin).','.$resId.pq_substr($result,$fin);
  	} else {
  	  $result=$res;
  	} 
  }
}

// Message of correct saving
displayLastOperationStatus($result);
?>
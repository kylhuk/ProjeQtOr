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
scriptLog('   ->/tool/saveResourceIncompatible.php');

$idResource= RequestHandler::getId('idResource');
$description = RequestHandler::getValue('resourceIncompatibleDescription');
$idResourceIncompatible = RequestHandler::getId('resourceIncompatible');
$idIncompatible = RequestHandler::getId('idIncompatible');
Sql::beginTransaction();
$result = "";

if(!$idIncompatible){
  $resourceIncompatible = new ResourceIncompatible();
  $resourceIncompatible->idResource = $idResource;
  $resourceIncompatible->idIncompatible = $idResourceIncompatible;
  $resourceIncompatible->description = nl2brForPlainText($description);
  $res=$resourceIncompatible->save();
  
  $resource = new ResourceIncompatible();
  $resource->idResource = $idResourceIncompatible;
  $resource->idIncompatible = $idResource;
  $resource->description = nl2brForPlainText($description);
  $res=$resource->save();
}else{
  $resourceIncompatible = new ResourceIncompatible($idIncompatible);
  $resource = new ResourceIncompatible();
  $incompatible = $resource->getSingleSqlElementFromCriteria('ResourceIncompatible', array('idIncompatible'=>$resourceIncompatible->idResource));
  $res=$incompatible->delete();
  $res=$resourceIncompatible->delete();
}

if($result == ""){
  $result = getLastOperationStatus($res);
}
// Message of correct saving
displayLastOperationStatus($res);

?>
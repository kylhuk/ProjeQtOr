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

/** ============================================================================
 * 
 */
require_once "../tool/projeqtor.php";

$idItemMailable = RequestHandler::getValue('idItemMailable');
if($idItemMailable != null){
  $name = SqlList::getFieldFromId('Mailable', $idItemMailable, 'name',false);
  $arrayFields = getObjectClassTranslatedFieldsList(pq_trim($name));
  $newArrayFields = array();
  foreach ($arrayFields as $elmt=>$val){
  	$newArrayFields[$elmt]=$val;
  	if(pq_substr($elmt, 0, 2) == "id" and pq_substr($elmt, 2) != "" and pq_substr($elmt, 0) != "idle"){
  		$newArrayFields['name'.pq_ucfirst(pq_substr($elmt, 2))]=$val.' ('.i18n('colName').')';
  	}
  }
  $newArrayFields['_item'] = i18n('mailableItem');
	$newArrayFields['_dbName'] = i18n('mailableDbName');
	$newArrayFields['_responsible'] = i18n('colResponsible').', '.i18n('synonymResponsible');
	$newArrayFields['_sender'] = i18n('mailableSender');
	$newArrayFields['_project'] = i18n('colIdProject').', '.i18n('synonymProject');
	$newArrayFields['_url'] = i18n('mailableUrl');
	$newArrayFields['_goto'] = i18n('mailableGoto');
	$newArrayFields['_HISTORY'] = i18n('mailableHistory');
	$newArrayFields['_LINK'] = i18n('mailableLink');
	$newArrayFields['_NOTE'] = i18n('mailableNote');
	$newArrayFields['_NOTESTD'] = i18n('mailableNoteTd');
  $newArrayFields['_allAttachments'] = i18n('mailableAttachments');
  $newArrayFields['_lastAttachment'] = i18n('mailableLastAttachments');
  if($name =="Meeting" OR  $name =="TestSession" OR $name =="Activity"){
    $newArrayFields['__ASSIGNMENT'] = i18n('colListAssignment');
  }  
}else{
  $newArrayFields['_id'] = 'id';
  $newArrayFields['_name'] = i18n('colName');
  $newArrayFields['_idProject'] = 'id'.i18n('colIdProject');
  $newArrayFields['_nameProject'] = i18n('colIdProject').' ('.i18n('colName').')';
  $newArrayFields['_description'] = 'colDescription';
  $newArrayFields['_item'] = i18n('mailableItem');
  $newArrayFields['_dbName'] = i18n('mailableDbName');
  $newArrayFields['_responsible'] = i18n('colResponsible').', '.i18n('synonymResponsible');
  $newArrayFields['_sender'] = i18n('mailableSender');
  $newArrayFields['_project'] = i18n('colIdProject').', '.i18n('synonymProject');
  $newArrayFields['_url'] = i18n('mailableUrl');
  $newArrayFields['_goto'] = i18n('mailableGoto');
  $newArrayFields['_HISTORY'] = i18n('mailableHistory');
  $newArrayFields['_LINK'] = i18n('mailableLink');
  $newArrayFields['_NOTE'] = i18n('mailableNote');
  $newArrayFields['_NOTESTD'] = i18n('mailableNoteTd');
  $newArrayFields['_allAttachments'] = i18n('mailableAttachments');
  $newArrayFields['_lastAttachment'] = i18n('mailableLastAttachments');
}
$sortArray = array();
foreach ($newArrayFields as $key=>$name){
	$pregName = htmlentities($name, ENT_NOQUOTES, 'utf-8');
	$pregName = preg_replace('#&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring);#', '\1', $pregName);
	$pregName = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $pregName);
	$pregName = preg_replace('#&[^;]+;#', '', $pregName);
	$sortArray[$key] = pq_strtolower($pregName);
}
asort($sortArray);
foreach ($newArrayFields as $key=>$name){
	$sortArray[$key]=$name;
}
$newArrayFields = $sortArray;
echo json_encode($newArrayFields);
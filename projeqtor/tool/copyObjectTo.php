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
 * Copy an object as a new one (of the same class) : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$fromContextMenu = RequestHandler::getBoolean('fromContextMenu');
$objectId = RequestHandler::getId('objectId');
$objectClass = RequestHandler::getClass('objectClass');

// Get the object from session(last status before change)
if($fromContextMenu){
  $obj=new $objectClass($objectId);
}else{
  $obj=SqlElement::getCurrentObject(null,null,true,false);
}
 if(! is_object($obj)) {
   if(RequestHandler::isCodeSet('copyClass') and RequestHandler::isCodeSet('copyId') and RequestHandler::getClass('copyClass')=='SubTask'){
     $obj= new SubTask (RequestHandler::getId('copyId'));
   }else{
     throwError('last saved object is not a real object');
   }
  
}

// Get the object class from request
if (! array_key_exists('copyClass',$_REQUEST)) {
  throwError('copyClass parameter not found in REQUEST');
}
$className=$_REQUEST['copyClass'];
// compare expected class with object class
if ($className!=get_class($obj) ) {
  if($className=='SubTask'and RequestHandler::isCodeSet('copyId')){
    $obj= new SubTask (RequestHandler::getId('copyId'));
  }else{
    throwError('last save object (' . get_class($obj) . ') is not of the expected class (' . $className . ').');
  }
 
}
if (! array_key_exists('copyToClass',$_REQUEST)) {
  throwError('copyToClass parameter not found in REQUEST');
}
if (is_numeric($_REQUEST['copyToClass'])) {
	$toClassNameObj=new Copyable($_REQUEST['copyToClass']); // validates copyToClass is numeric inside SqlElement constructor
	$toClassName=$toClassNameObj->name;
} else {
	$toClassName=$_REQUEST['copyToClass'];
}
if (! array_key_exists('copyToName',$_REQUEST)) {
  throwError('copyToName parameter not found in REQUEST');
}
$toName=$_REQUEST['copyToName'];
if($className=='SubTask'){
  if (isTextFieldHtmlFormatted($toName)) {
    $text=new Html2Text($toName);
    $toName=$text->getText();
  } else {
    $toName=br2nl($toName);
  }
  $toName=pq_str_replace('"','""',$toName);
  $toName=pq_substr($toName,0,100);
}

$copyToOrigin=false;
if (array_key_exists('copyToOrigin',$_REQUEST)) {
  $copyToOrigin=true;
}
$copyToLinkOrigin=false;
if (array_key_exists('copyToLinkOrigin',$_REQUEST)) {
  $copyToLinkOrigin=true;
}
$synchronizationLink=false;
if (array_key_exists('synchronizationLinkCopy',$_REQUEST)) {
  $synchronizationLink=true;
}
if($className != "CatalogUO"){
if (! array_key_exists('copyToType',$_REQUEST)) {
  throwError('copyToType parameter not found in REQUEST');
}
$toType=$_REQUEST['copyToType'];
}else{
  $toType = null;
}
$copyToWithNotes=false;
if (array_key_exists('copyToWithNotes',$_REQUEST)) {
  $copyToWithNotes=true;
}
$copyToWithAttachments=false;
if (array_key_exists('copyToWithAttachments',$_REQUEST)) {
  $copyToWithAttachments=true;
}
$copyToWithLinks=false;
if (array_key_exists('copyToWithLinks',$_REQUEST)) {
  $copyToWithLinks=true;
}

$copyToWithSubTask=false;
if (array_key_exists('copyToWithSubTask',$_REQUEST)) {
  $copyToWithSubTask=true;
}

$copyToWithResult=false;
if (array_key_exists('copyToWithResult',$_REQUEST)) {
  $copyToWithResult=true;
}

$copyWithStructure=false;
if (array_key_exists('copyWithStructure',$_REQUEST)) {
	if ($className=='Activity' && $toClassName=='Activity') {
    $copyWithStructure=true; 
	}
}
$copyAssignments=false;
if (array_key_exists('copyWithAssignments',$_REQUEST)) {
	$copyAssignments=true;
}
if (array_key_exists('copyToCopyVersionStructure',$_REQUEST)) {
	$copyVersionStructure=$_REQUEST['copyToCopyVersionStructure'];
	$obj->_copyVersionStructure=$copyVersionStructure;
}
if (array_key_exists('copyToVersionNumber',$_REQUEST)) {
	$copyToVersionNumber=$_REQUEST['copyToVersionNumber'];
	$obj->versionNumber=$copyToVersionNumber;
}

$toProject=(property_exists($obj, 'idProject'))?$obj->idProject:null;
if (RequestHandler::isCodeSet('copyToProject')) {
  $toProject=RequestHandler::getId('copyToProject',false,null);
}

$copyStructure = RequestHandler::getValue('copyStructure');
$duplicateLinkedTestsCases = RequestHandler::getValue('duplicateLinkedTestsCases');
if($copyToWithLinks and $duplicateLinkedTestsCases){
  $copyToWithLinks = false;
}
$copyToWithStatus = (RequestHandler::getBoolean('copyToWithStatus') == 1)?true:false;

$copyToWithDetail = (RequestHandler::getBoolean('copyToWithDetail') == 1)?true:false;

$moveAfterCreate = RequestHandler::getId('moveAfterCreate');

$copyToWithDependency=RequestHandler::getBoolean('copyToWithDependency');
$copyToWithPredecessor=RequestHandler::getBoolean('copyToWithPredecessor');
$copyToWithSuccessor=RequestHandler::getBoolean('copyToWithSuccessor');

Sql::beginTransaction();
PlanningElement::$_noDispatch=true;
SqlElement::$_doNotSaveLastUpdateDateTime=true;
$error=false;
// copy from existing object
Security::checkValidId($toType); // $toType is an id !
if ($className=='ComponentVersion') {
	$obj->name=$toName;
	$obj->idComponentVersionType=$toType;
	$newObj=$obj->copy();
	if ($copyToWithStatus) {
	  $allowedStatusList = Workflow::getAllowedStatusListForObject(new ComponentVersion());
	  if(isset($allowedStatusList) and count($allowedStatusList) > 0){
	    $st = reset($allowedStatusList);
	    $newObj->idStatus = $st->id;
	    $resultSaveStatus=$newObj->save();
	  }
	}
}elseif($className=='Asset' or $className=='SubTask'){
  $newObj=$obj->copyTo($toClassName,$toType, $toName, $toProject,$copyStructure,$copyToWithNotes, $copyToWithAttachments,$copyToWithLinks, false, false, null, null, false, false, false, false, $moveAfterCreate);
}else if ($className=="ProjectExpense" or $className=="IndividualExpense") {
  $newObj=$obj->copyTo($toClassName,$toType, $toName, $toProject, $copyToOrigin, $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks, $copyAssignments,false,null,null,$copyToWithResult, false, $copyToWithStatus, false, $copyToWithDetail);
}else if($className == 'Activity'){
  $newObj=$obj->copyTo($toClassName,$toType, $toName, $toProject, $copyToOrigin, $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks, $copyAssignments,false,null,null,$copyToWithResult, false, $copyToWithStatus, false, $moveAfterCreate, $copyToWithDependency, $copyToWithPredecessor, $copyToWithSuccessor);
}else {
  if(property_exists(get_class($obj), '_SubTask')){
    $newObj=$obj->copyTo($toClassName,$toType, $toName, $toProject, $copyToOrigin, $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks, $copyAssignments,false,null,null,$copyToWithResult, false, $copyToWithStatus,$copyToWithSubTask,$moveAfterCreate);
  }else{
    $newObj=$obj->copyTo($toClassName,$toType, $toName, $toProject, $copyToOrigin, $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks, $copyAssignments,false,null,null,$copyToWithResult, false, $copyToWithStatus, false, $moveAfterCreate);
  }
  
}
$result=$newObj->_copyResult;
if (! pq_stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
  $error=true;
}
unset($newObj->_copyResult);
if (!$error and $copyWithStructure and get_class($obj)=='Activity' and get_class($newObj)=='Activity') {
	//$res=copyStructure($obj, $newObj, $copyToOrigin, $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks, $copyAssignments);
	$res=PlanningElement::copyStructure($obj, $newObj, $copyToOrigin, $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks, $copyAssignments, null, $toProject, false, $copyWithStructure, false, $copyToWithStatus);
	if ($res!='OK') {
	  $error=true;
	  $result=$res;
	} else {
	  //PlanningElement::copyStructureFinalize(); // PBER #9515 - With this line, dependencies are dupplicated
	                                              //              copyStructureFinalize() is done just below
	}
}
if (!$error and ($copyWithStructure or $copyAssignments)) {
  PlanningElement::copyStructureFinalize();
}
if (!$error and $copyToLinkOrigin) {
	$link=new Link();
  $link->ref1Id=$obj->id;
  $link->ref1Type=get_class($obj);
  $link->ref2Id=$newObj->id;
  $link->ref2Type=get_class($newObj);
  if ($synchronizationLink=='on'){
    $Lnk=new Link();
    $where1 = "(ref1Type='$link->ref1Type' AND ref1Id='$link->ref1Id' AND idSynchronizationItem IS NOT NULL) OR (ref1Type='$link->ref2Type' AND ref1Id='$link->ref2Id' AND idSynchronizationItem IS NOT NULL)";
    $where2 = "(ref2Type='$link->ref2Type' AND ref2Id='$link->ref2Id' AND idSynchronizationItem IS NOT NULL) OR (ref2Type='$link->ref1Type' AND ref2Id='$link->ref1Id' AND idSynchronizationItem IS NOT NULL)";
    $syncCheck1 = $Lnk->getSqlElementsFromCriteria(null, false, $where1);
    $syncCheck2 = $Lnk->getSqlElementsFromCriteria(null, false, $where2);
    if ($syncCheck1 || $syncCheck2) {
      $result = '<b>' . i18n('errorElementAlreadySynchronized') . '</b>';
      $result .= '<input type="hidden" id="lastOperation" value="control" />';
      $result .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
      displayLastOperationStatus($result);
      return;
    }
    $synchronizedItems = new SynchronizedItems();
    $synchronizedItems->ref1Id = $link->ref1Id;
    $synchronizedItems->ref1Type = $link->ref1Type;
    $synchronizedItems->ref2Id = $link->ref2Id;
    $synchronizedItems->ref2Type = $link->ref2Type;
    $synchronizedItems->save();
    $link->idSynchronizationItem = $synchronizedItems->id;
  }else{
    $link->idSynchronizationItem = null;
  }
  $link->comment=null;
  $user=getSessionUser();
  $link->idUser=$user->id;
  $link->creationDate=date("Y-m-d H:i:s"); 
  $resLink=$link->save();
}

if(!$error and $copyStructure and get_class($obj)=='Requirement') {
  $res=Requirement::copyStructure($obj, $newObj, $copyToOrigin, $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks, $copyAssignments, null, $toProject, false, $duplicateLinkedTestsCases, $copyToWithStatus);
}
if(!$error and $duplicateLinkedTestsCases and get_class($obj)=='Requirement'){
  $link = new Link();
  $listLink = $link->getSqlElementsFromCriteria(array('ref1Type'=>'Requirement','ref1Id'=>$obj->id,'ref2Type'=>'TestCase'));
  foreach ($listLink as $lk){
    $tc = new TestCase($lk->ref2Id);
    $newTc = $tc->copy();
    $newTc->save();
    $newLink = $lk->copy();
    $newLink->ref1Id = $newObj->id;
    $newLink->ref2Id = $newTc->id;
    $newLink->save();
  }
}
// Message of correct saving
$status = displayLastOperationStatus($result);
if ($status == "OK") {
  if (! array_key_exists('comboDetail', $_REQUEST)) {
    SqlElement::setCurrentObject ($newObj);
  }
}

/*function copyStructure($from, $to, $copyToOrigin, $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks, $copyAssignments) {
  	$nbErrors=0;
  	$errorFullMessage="";
  	$milArray=array();
    $milArrayObj=array();
    $actArray=array();
    $actArrayObj=array();
    $crit=array('idActivity'=>$from->id);
    $items=array();
    // Activities to be copied
    $activity=New Activity();
    $activities=$activity->getSqlElementsFromCriteria($crit, false, null, null, true);
    foreach ($activities as $activity) {
      $act=new Activity($activity->id);
      $items['Activity_'.$activity->id]=$act;
    }
    $mile=New Milestone();
    $miles=$mile->getSqlElementsFromCriteria($crit, false, null, null, true);
    foreach ($miles as $mile) {
      $mil=new Milestone($mile->id);
      $items['Milestone_'.$mile->id]=$mil;
    }
    // Sort by wbsSortable
    uasort($items, "customSortByWbsSortable");
    $itemArrayObj=array();
    $itemArray=array();
    $itemArrayAssignment=array();
    foreach ($items as $id=>$item) {
      //$new=$item->copy();
      //$toTypeFld='id'.get_class($item).'Type';
      $new=$new=$item->copy();
      $tmpRes=$new->_copyResult;
      if (! pq_stripos($tmpRes,'id="lastOperationStatus" value="OK"')>0 ) {
        errorLog($tmpRes);
        $errorFullMessage.='<br/>'.i18n(get_class($item)).' #'.htmlEncode($item->id)." : ".$tmpRes;
        $nbErrors++;
      } else {
        $itemArrayObj[get_class($new) . '_' . $new->id]=$new;
        $itemArray[$id]=get_class($new) . '_' . $new->id;
        if ($copyAssignments and property_exists($item, '_Assignment')) {
  	      $itemArrayAssignment[]=array('class'=>get_class($item),'oldId'=>$item->id,'newId'=>$new->id);
  	    }
      }
    }
    foreach ($itemArrayObj as $new) {
      $new->idProject=$from->idProject;
      $new->idActivity=$to->id;
      $pe=get_class($new).'PlanningElement';
      $new->$pe->wbs=null;
      $tmpRes=$new->save();
      if (! pq_stripos($tmpRes,'id="lastOperationStatus" value="OK"')>0 ) {
        errorLog($tmpRes);
        $errorFullMessage.='<br/>'.i18n(get_class($new)).' #'.htmlEncode($new->id)." : ".$tmpRes;
        $nbErrors++;
      } 
    }
    if ($copyAssignments) {
      foreach ($itemArrayAssignment as $item) {
        $ass=new Assignment();
        $crit=array('refType'=>$item['class'], 'refId'=>$item['oldId']);
        $lstAss=$ass->getSqlElementsFromCriteria($crit);
        foreach ($lstAss as $ass) {
          $ass->id=null;
          $ass->idProject=$from->idProject;
          $ass->refId=$item['newId'];
          $ass->comment=null;
          $ass->realWork=0;
          $ass->leftWork=$ass->assignedWork;
          $ass->plannedWork=$ass->assignedWork;
          $ass->realStartDate=null;
          $ass->realEndDate=null;
          $ass->plannedStartDate=null;
          $ass->plannedEndDate=null;
          $ass->realCost=0;
          $ass->leftCost=$ass->assignedCost;
          $ass->plannedCost=$ass->assignedCost;
          $ass->billedWork=null;
          $ass->idle=0;
          $ass->save();
        }
      }
    }
    // Copy dependencies 
    $critWhere="";
    foreach ($itemArray as $id=>$new) {
      $split=pq_explode('_',$id);
      $critWhere.=($critWhere)?', ':'';
      $critWhere.="('" . $split[0] . "','" . Sql::fmtId($split[1]) . "')";
    }
    if ($critWhere) {
      $clauseWhere="(predecessorRefType,predecessorRefId) in (" . $critWhere . ")"
           . " or (successorRefType,successorRefId) in (" . $critWhere . ")";
    } else {
      $clauseWhere=" 1=0 ";
    }
    $dep=New dependency();
    $deps=$dep->getSqlElementsFromCriteria(null, false, $clauseWhere);
    foreach ($deps as $dep) {
      if (array_key_exists($dep->predecessorRefType . "_" . $dep->predecessorRefId, $itemArray) ) {
        $split=pq_explode('_',$itemArray[$dep->predecessorRefType . "_" . $dep->predecessorRefId]);
        $dep->predecessorRefType=$split[0];
        $dep->predecessorRefId=$split[1];
        $crit=array('refType'=>$split[0], 'refId'=>$split[1]);
        $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $crit);
        $dep->predecessorId=$pe->id;
      }
      if (array_key_exists($dep->successorRefType . "_" . $dep->successorRefId, $itemArray) ) {
        $split=pq_explode('_',$itemArray[$dep->successorRefType . "_" . $dep->successorRefId]);
        $dep->successorRefType=$split[0];
        $dep->successorRefId=$split[1];
        $crit=array('refType'=>$split[0], 'refId'=>$split[1]);
        $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $crit);
        $dep->successorId=$pe->id;
      }
      $dep->id=null;
      $tmpRes=$dep->save();
      if (! pq_stripos($tmpRes,'id="lastOperationStatus" value="OK"')>0 ) {
        errorLog($tmpRes);
        $errorFullMessage.='<br/>'.i18n(get_class($dep)).' #'.htmlEncode($dep->id)." : ".$tmpRes;
        $nbErrors++;
      } 
    }
    $result="OK";
    if ($nbErrors>0) {
      $result='<div class="messageERROR" >' 
             . i18n('errorMessageCopy',array($nbErrors))
             . '</div><br/>'
             . pq_str_replace('<br/><br/>','<br/>',$errorFullMessage);
    }
    return $result;
}
function customSortByWbsSortable($a,$b) {
  $pe=get_class($a).'PlanningElement';
  $wbsA=$a->$pe->wbsSortable;
  $pe=get_class($b).'PlanningElement';
  $wbsB=$b->$pe->wbsSortable;
  return ($wbsA > $wbsB)?1:-1;
}*/
?>
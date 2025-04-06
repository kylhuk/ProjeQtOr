<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2018 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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

require_once "../tool/projeqtor.php";
$operation=RequestHandler::getValue('operation');
$force=RequestHandler::getBoolean('forceDesactivate');
if ($operation=='saveDefinition') {
  cronSaveDefinition();
} else if ($operation=='activate') {
  cronActivate($force);
}

function cronPlanningDifferential(){
  $user=new User();//getSessionUser();
  $user->idProfile=1; // Admin
  $user->resetAllVisibleProjects();
  setSessionUser($user);
  SqlList::cleanAllLists();
  $startDatePlan=cronPlanningStartDate(Parameter::getGlobalParameter("automaticPlanningDifferentialDate"));
  $modeSurbookingDifferential=Parameter::getGlobalParameter("modeSurbookingDifferential");
  $arrayProj=array();
  $pe=new PlanningElement();
  $lst=$pe->getSqlElementsFromCriteria(array('refType'=>'Project','needReplan'=>1));
  foreach ($lst as $pe) {
    $arrayProj[]=$pe->refId;
  }
  $mode=i18n("paramAutomaticPlanningDifferential");
  $mode=pq_str_replace(array("<b>","</b>"),array("",""),$mode);
  traceLog(i18n("sectionAutomaticPlanning").' : '.$mode." - ".i18n('colStart')." - ".i18n('projects').' : ' .((count($arrayProj))?implode(',',$arrayProj):i18n('paramNone')));
  if (count($arrayProj)>0) {
    //Sql::beginTransaction(); #3601 : management of transaction in now included in PlannedWork::plan()
    if($modeSurbookingDifferential=="YES"){
    $result=PlannedWork::plan($arrayProj, $startDatePlan, false, true);
    }else{
    $result=PlannedWork::plan($arrayProj, $startDatePlan, false);
    }
    $status = getLastOperationStatus ( $result );
    //if ($status == "OK" or $status=="NO_CHANGE" or $status=="INCOMPLETE") {
    //  Sql::commitTransaction ();
    //} else {
    //  Sql::rollbackTransaction ();
    //}
    if ($status == "OK") $resultStatus=i18n("planningResultOK");
    else if ($status == "NO_CHANGE" or $status == "INCOMPLETE") $resultStatus=i18n("planningResultNoChange");
    else $resultStatus=i18n("planningResultError");
    traceLog(i18n("sectionAutomaticPlanning").' : '.$mode." - ".i18n('colEnd')." - ". i18n("colResult"). " = $resultStatus");
  } else {
    $status='NO_CHANGE';
  }
}

function cronPlanningComplete(){
  $user=new User();//getSessionUser();
  $user->idProfile=1; // Admin
  $user->resetAllVisibleProjects();
  setSessionUser($user);
  SqlList::cleanAllLists();
  $startDatePlan=cronPlanningStartDate(Parameter::getGlobalParameter("automaticPlanningCompleteDate"));
  $enterPlannedAsReal=Parameter::getGlobalParameter('automaticFeedingOfTheReal');
  $modeSurbookingComplete=Parameter::getGlobalParameter("modeSurbookingComplete");
  if ($enterPlannedAsReal=='YES') {
    PlannedWork::enterPlannedWorkAsReal(null, $startDatePlan);  
  }
  
  $mode=i18n("paramAutomaticPlanningComplete");
  $mode=pq_str_replace(array("<b>","</b>"),array("",""),$mode);
  traceLog(i18n("sectionAutomaticPlanning").' : '.$mode." - ".i18n('colStart')." - ".i18n('projects').' : '.i18n('all'));
  //Sql::beginTransaction(); #3601 : management of transaction in now included in PlannedWork::plan()
  $modeSurbookingComplete = ($modeSurbookingComplete=="YES")?true:false;
  $result=PlannedWork::plan(array(' '), $startDatePlan, false, $modeSurbookingComplete);
  $status = getLastOperationStatus ( $result );
  //if ($status == "OK" or $status=="NO_CHANGE" or $status=="INCOMPLETE") {
  //  Sql::commitTransaction ();
  //} else {
  //  Sql::rollbackTransaction ();
  //}
  if ($status == "OK") $resultStatus=i18n("planningResultOK");
  else if ($status == "NO_CHANGE" or $status == "INCOMPLETE") $resultStatus=i18n("planningResultNoChange");
  else $resultStatus=i18n("planningResultError");
  traceLog(i18n("sectionAutomaticPlanning").' : '.$mode." - ".i18n('colEnd')." - ". i18n("colResult"). " = $resultStatus");
}

function cronPlanningStartDate($param) {
  if ($param=="W") {
    return date('Y-m-d',firstDayofWeek()); // Call with no parameter will return first day of current week
  } else if ($param=="M") {
    return date('Y-m').'-01';
  } else if (pq_substr($param,0,1)=='J') {
    $day=pq_substr($param,1);
    if ( is_numeric($day) and $day!=0) {
      return addDaysToDate(date('Y-m-d'), $day);
    } else {
      return date('Y-m-d');
    }
  } else {
    return date('Y-m-d',firstDayofWeek());
  }
}

function cronSaveDefinition() {
  $minutes=RequestHandler::getValue('cronDefinitonMinutes');
  $hours=RequestHandler::getValue('cronDefinitonHours');
  $dayOfMonth=RequestHandler::getValue('cronDefinitonDayOfMonth');
  $month=RequestHandler::getValue('cronDefinitonMonth');
  $dayOfWeek=RequestHandler::getValue('cronDefinitonDayOfWeek');
  $checkMail=RequestHandler::getValue('cronConsistencyCheckMail');
  
  $scope=RequestHandler::getValue('cronExecutionScope');
  $cronExecution=CronExecution::getObjectFromScope($scope);

  $cronStr=$minutes.' '.$hours.' '.$dayOfMonth.' '.$month.' '.$dayOfWeek;
  $cronExecution->idle=1; // Desactivate after save (will force reactivate and then CRON relaunch
  
  if (! $cronExecution->fileExecuted) {
    if (pq_substr($scope,0,15)=='imputationAlert') {
      $cronExecution->fileExecuted="../tool/generateImputationAlert.php";
    } else {
      $cronExecution->fileExecuted="../tool/cronExecutionStandard.php";
    }
  }
  if (! $cronExecution->fonctionName) $cronExecution->fonctionName="cron". pq_ucfirst($scope);
  $cronExecution->cron=$cronStr;
  $cronExecution->nextTime=null;
  $result=$cronExecution->save();
  if ($scope=='runConsistencyCheck'){
    Parameter::storeGlobalParameter('cronConsistencyCheckMail', $checkMail);
  }
}

function cronActivate($forceDesactivate=false) {
  $scope=RequestHandler::getValue('cronExecutionScope');
  $cronExecution=CronExecution::getObjectFromScope($scope);
  $cronExecution->idle=($forceDesactivate or $cronExecution->idle==0)?1:0;
  $cronExecution->nextTime=null;
  $result=$cronExecution->save();
}

function dataCloningCheckRequest(){
  $dataCloning = new DataCloning();
  $dataCloningList = $dataCloning->getSqlElementsFromCriteria(array('idle'=>'0'));
  foreach ($dataCloningList as $data){
    if(!$data->isActive and !$data->isRequestedDelete and !$data->codeError){
      $data->createDataCloning($data->id);
    }
    if($data->isRequestedDelete){
      $data->deleteDataCloning($data->id);
    }
  }
}

//florent
function archiveHistory(){
  $hist=new History();
  $histArch= new HistoryArchive();
  $timeToArchive=Parameter::getGlobalParameter('cronArchiveTime');
  $startArchive=Parameter::getGlobalParameter('cronArchivePlannedDate');
  $tableHist=$hist->getDatabaseTableName();
  $tableHistArch=$histArch->getDatabaseTableName();
  $archivDate = date('Y-m-d', pq_strtotime("-".$timeToArchive." day"));
  $colList="";
  foreach ($hist as $fld=>$val) {
    if (pq_substr($fld,0,1)=='_' or $fld=='id') continue;
    $col=$hist->getDatabaseColumnName($fld);
    if ($col) {
      $colList.="$col, ";
    }
  }
  $colList=pq_substr($colList,0,-2);
  $requestIns="INSERT INTO $tableHistArch ($colList)\n"
      ."SELECT $colList FROM $tableHist WHERE operationDate < '".$archivDate."'"; //   INSERT INTO `archivehistory` (`idHistory`,`refType`,`refId`,`operation`,`colName`,`oldValue`,`newValue`,`operationDate`,`isWorkHistory`,`idUser`) 
  $clauseDel="operationDate < '".$archivDate."'";
  SqlDirectElement::execute($requestIns);
  $res=Sql::$lastQueryNbRows;
  if($res > 0){
    $hist->purge($clauseDel);
  }
}
function kpiCalculate() {
  $time=date("Y-m-d H:00:00");
  KpiValueRequest::triggerCalculation($time);
}

function cronCloseMails(){
  $maintenanceCloseMail=SqlElement::getSingleSqlElementFromCriteria('Parameter', array('parameterCode'=>'maintenanceCloseMail'));
  if($maintenanceCloseMail->id=='')$nbDays=7;
  else $nbDays=$maintenanceCloseMail->parameterValue;
  $targetDate=addDaysToDate(date('Y-m-d'), (-1)*$nbDays ) . ' ' . date('H:i:s');
  $item='Mail';
  $obj=new $item();
  $clauseWhere="mailDateTime<'" . $targetDate . "'";
  $obj->close($clauseWhere);
}

function cronDeleteMails(){
  $maintenanceDeleteMail=SqlElement::getSingleSqlElementFromCriteria('Parameter', array('parameterCode'=>'maintenanceDeleteMail'));
  if($maintenanceDeleteMail->id=='')$nbDays=30;
  else $nbDays=$maintenanceDeleteMail->parameterValue;
  $targetDate=addDaysToDate(date('Y-m-d'), (-1)*$nbDays ) . ' ' . date('H:i:s');
  $item='Mail';
  $obj=new $item();
  $clauseWhere="mailDateTime<'" . $targetDate . "'";
  return $obj->purge($clauseWhere);
}

function cronCloseAlerts(){
  $maintenanceCloseAlert=SqlElement::getSingleSqlElementFromCriteria('Parameter', array('parameterCode'=>'maintenanceCloseAlert'));
  if($maintenanceCloseAlert->id=='')$nbDays=7;
  else $nbDays=$maintenanceCloseAlert->parameterValue;
  $targetDate=addDaysToDate(date('Y-m-d'), (-1)*$nbDays ) . ' ' . date('H:i:s');
  $item='Alert';
  $obj=new $item();
  $clauseWhere="alertInitialDateTime<'" . $targetDate . "'";
  $obj->read($clauseWhere);
  $obj->close($clauseWhere);
}
function cronDeleteAlerts(){
  $maintenanceDeleteAlert=SqlElement::getSingleSqlElementFromCriteria('Parameter', array('parameterCode'=>'maintenanceDeleteAlert'));
  if($maintenanceDeleteAlert->id=='')$nbDays=30;
  else $nbDays=$maintenanceDeleteAlert->parameterValue;
  $targetDate=addDaysToDate(date('Y-m-d'), (-1)*$nbDays ) . ' ' . date('H:i:s');
  $item='Alert';
  $obj=new $item();
  $clauseWhere="alertInitialDateTime<'" . $targetDate . "'";
  $obj->purge($clauseWhere);
}

function cronDeleteNotifications(){
  $maintenanceDeleteNotification=SqlElement::getSingleSqlElementFromCriteria('Parameter', array('parameterCode'=>'maintenanceDeleteNotification'));
  if($maintenanceDeleteNotification->id=='')$nbDays=30;
  else $nbDays=$maintenanceDeleteNotification->parameterValue;
  $targetDate=addDaysToDate(date('Y-m-d'), (-1)*$nbDays );
  $item='Notification';
  $obj=new $item();
  $clauseWhere="notificationDate<'" . $targetDate . "'";
  $obj->purge($clauseWhere);
}

function cronDeleteAudit(){
  $maintenanceDeleteAudit=SqlElement::getSingleSqlElementFromCriteria('Parameter', array('parameterCode'=>'maintenanceDeleteAudit'));
  if($maintenanceDeleteAudit->id=='')$nbDays=30;
  else $nbDays=$maintenanceDeleteAudit->parameterValue;
  $targetDate=addDaysToDate(date('Y-m-d'), (-1)*$nbDays ) . ' ' . date('H:i:s');
  $item='Audit';
  $obj=new $item();
  $clauseWhere="disconnectionDateTime<'" . $targetDate . "'";
  $obj->purge($clauseWhere);
}

function cronDeleteLogfile(){
  $maintenanceDeletedLogfile=SqlElement::getSingleSqlElementFromCriteria('Parameter', array('parameterCode'=>'deleteLogfileDays'));
  if($maintenanceDeletedLogfile->id=='')$nbDays=30;
  else $nbDays=$maintenanceDeletedLogfile->parameterValue;
  $clauseWhere=addDaysToDate(date('Y-m-d'), (-1)*$nbDays ) . ' ' . date('H:i:s');
  $item='Logfile';
  $obj=new $item();
  $obj->purge($clauseWhere);
}

function cronDisconnectAll(){
  $audit=new Audit();
  $list=$audit->getSqlElementsFromCriteria(array("idle"=>"0"));
  foreach($list as $audit) {
	  $audit->requestDisconnection=1;
	  $audit->save();
    $userAudit=new User($audit->idUser);
    if ($userAudit->id) {
      $userAudit->cleanCookieHash();
      $userAudit->stopAllWork();
    }
  }
}

function purgeGhostSessions() {
  $audit=new Audit();
  $date=date('Y-m-d');
  $time=date('H:i:s');
  $checkDateTime=addDaysToDate($date, -1).' '.$time;
  $list=$audit->getSqlElementsFromCriteria(null,null,"idle=0 and lastAccessDateTime<='$checkDateTime'");
  foreach($list as $audit) {
    $audit->disconnectionDateTime = $audit->lastAccessDateTime;
		$audit->idle = 1;
		$audit->sessionId=$audit->sessionId.'_'.date('YmdHis');
		enableCatchErrors(); 
		enableSilentErrors();
		$audit->save();
		$userAudit=new User($audit->idUser);
		if ($userAudit->id) {
		  $userAudit->cleanCookieHash();
		  $userAudit->stopAllWork();
		}
    disableCatchErrors();
    disableSilentErrors();
  }
}

function cronBaseline() {
  $baseline=new Baseline();
  $baseline->idProject='0';
  $baseline->baselineDate=date('Y-m-d');
  $baseline->name='automaticGlobalBaseline';
  $baseline->idUser=getSessionUser()->id; 
  $baseline->creationDateTime=date('Y-m-d H:i:s');
  $baseline->idPrivacy=9;
  $res=new Resource(getSessionUser()->id);
  $baseline->idTeam=$res->idTeam;
  $baseline->baselineNumber=intval($baseline->countSqlElementsFromCriteria(null,"name='$baseline->name' and idProject=0 and baselineDate<>'$baseline->baselineDate'"))+1;
  $baseline->saveWithPlanning();
}

function cronRunConsistencyFix(){
  $correct=1;
  IndicatorValue::$_doNotUpdate=true;
  SqlElement::$_skipAllControls=true;
  Consistency::checkWbs($correct,false,false);
  Consistency::checkBbs($correct,false,false);
  Consistency::checkSbs($correct,false,false);
  Consistency::checkDuplicateWork($correct,false,false);
  Consistency::checkWorkOnTicket($correct,false,false);
  Consistency::checkWorkOnAssignment($correct,false,false);
  Consistency::checkIdlePropagation($correct,false,false);
  Consistency::checkMissingPlanningElement($correct,false,false);
  Consistency::checkWorkOnActivity($correct,false,false);
  Consistency::checkWorkOnMeeting($correct,false,false);
  Consistency::checkPeriodicMeetingAssign($correct,false,false);
  Consistency::checkBudget($correct,false,false);
  Consistency::checkInvalidFilters($correct,false,false);
  Consistency::checkPools($correct,false,false);
  Consistency::checkProject($correct,false,false);
  Consistency::checkAssignmentSelection($correct,false,false);
  IndicatorValue::$_doNotUpdate=false;
  SqlElement::$_skipAllControls=false;
}

function cronRunConsistencyCheck(){
  $correct=0;
  require_once '../tool/adminFunctionalities.php';
  $title=Parameter::getGlobalParameter('paramDbDisplayName')." - ".i18n('consistencyCheckSection').htmlFormatDateTime(date(" Y-m-d H:i"));
  $to=Parameter::getGlobalParameter('cronConsistencyCheckMail');
  ob_start();
  echo "<html><head><style> ";
  echo ".messageOK, .messageERROR{top:0px;overflow:hidden;vertical-align: middle;padding: 5px 5px;color:#555555;border:0px;border-radius: 10px;min-height:16px; max-height:200px;min-width: 100px;overflow-y:auto; overflox-x: hidden;width:95%;height:100%;}";
  echo "span.messageOK {background: #DDFFDD !important;font-weight: 600;}";
  echo ".messageERROR {background: #FF0000 !important;color: #FFFFFF !important;text-shadow: 1px 1px #555555; }";
  echo ".consistencySection {width:95%;background-color:#A0A0C0;color: white;margin:20px 5px;padding:5px 10px;font-size:150%;font-weight:bold;text-align:center;border:0;border-radius: 20px;}";
  echo "</style></head><body>";
  Sql::beginTransaction();
  IndicatorValue::$_doNotUpdate=true;
  SqlElement::$_skipAllControls=true;
  echo "<div class='consistencySection'>".i18n('sectionCheckWbs')."</div>";
  Consistency::checkWbs($correct,false,true);
  echo "<div class='consistencySection'>".i18n('sectionCheckBbs')."</div>";
  Consistency::checkBbs($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckSbs')."</div>";
  Consistency::checkSbs($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckWorkDuplicate')."</div>";
  Consistency::checkDuplicateWork($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckWorkOnTicket')."</div>";
  Consistency::checkWorkOnTicket($correct, false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckWorkOnAssignment')."</div>";
  Consistency::checkWorkOnAssignment($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckIdlePropagation')."</div>";
  Consistency::checkIdlePropagation($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckMissingPlanningElement')."</div>";
  Consistency::checkMissingPlanningElement($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckWorkOnActivity')."</div>";
  Consistency::checkWorkOnActivity($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckWorkOnMeeting')."</div>";
  Consistency::checkWorkOnMeeting($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckAssignmentOnPeriodicMeeting')."</div>";
  Consistency::checkPeriodicMeetingAssign($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckBudget')."</div>";
  Consistency::checkBudget($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckTechnicalData')."</div>";
  Consistency::checkInvalidFilters($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckPool')."</div>";
  Consistency::checkPools($correct,false,true);
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection'>".i18n('sectionCheckProject')."</div>";
  Consistency::checkProject($correct,false,true); 
  Sql::commitTransaction();
  Sql::beginTransaction();
  echo "<div class='consistencySection' style=''>".i18n('sectionCheckAssignmentSelection')."</div>";
  Consistency::checkAssignmentSelection($correct, false);
  Sql::commitTransaction();
  Sql::beginTransaction();
  IndicatorValue::$_doNotUpdate=false;
  SqlElement::$_skipAllControls=false;
  echo "</body></html>";
  $mailContent = ob_get_clean();
  echo $mailContent;
  sendMail($to,$title,$mailContent);
}

function cronGlobalRenumberWbs() {
  projeqtor_set_time_limit(900);
  IndicatorValue::$_doNotUpdate=true;
  traceLog("=== Global renumber of WBS structure === Start ");
  $plan=new PlanningElement();
  $plan->renumberWbs(true);
  traceLog("=== Global renumber of WBS structure === End ");
  IndicatorValue::$_doNotUpdate=false;
}

function cronSubscriptionUpdateRevision(){
  $revisionUpdateType = Parameter::getGlobalParameter('revisionUpdateType');
  if($revisionUpdateType == 'automatique'){
    traceLog("Update Revision start at ".date('Y-m-d H:i:s'));
    // Get available revision from projeqtor server //
    $jsonRevisionUpdate=RevisionUpdate::getRemoteFile("https://subscription.projeqtor.org/getRevisionUpdates.php");
    $arrayRevisionUpdate=json_decode($jsonRevisionUpdate,true);
    if (! is_array($arrayRevisionUpdate)) {
      $arrayRevisionUpdate=array();
    }
    if(count($arrayRevisionUpdate) > 0){
      ksort($arrayRevisionUpdate);
      $user=getSessionUser();
      $user->_arrayRevisionUpdate = $arrayRevisionUpdate;
    }
    // End get available revision from projeqtor server //
    if (! $arrayRevisionUpdate or count($arrayRevisionUpdate)==0) traceLog(" => no update available");
    // Download and install revision update //
    $zipSucces = false;
    $installSuccess = false;
    $filesList = array();
    if($arrayRevisionUpdate){
      foreach ($arrayRevisionUpdate as $revision=>$update){
        foreach ($update['files'] as $files){
          if(isset($filesList[$files['path']])){
            $filesInfo = $filesList[$files['path']];
          }else{
            $filesInfo = array();
          }
          if(!isset($filesInfo['revision'])){
            $filesInfo['revision']=array();
          }
          if(!isset($filesInfo['name'])){
            $filesInfo['name']=$files['name'];
          }
          if(!in_array($revision, $filesInfo['revision'])){
            array_push($filesInfo['revision'], $revision);
          }
          $filesList[$files['path']] = $filesInfo;
        }
      }
      if ($filesList) traceLog(" => ".count($filesList)." files to update");
      if($filesList and count($filesList) > 0){
        RevisionUpdate::deleteLockFile();
        $zipSucces = RevisionUpdate::downloadRevisionFiles($arrayRevisionUpdate, $filesList, true);
      }
      if($zipSucces){
        $installSuccess = RevisionUpdate::installRevisionUpdate($arrayRevisionUpdate);
        if($installSuccess){
          traceLog($installSuccess);
        }
      }
      $user=new User();
      $userList=$user->getSqlElementsFromCriteria(null);
      foreach ($userList as $user) {
        $user->_arrayRevisionUpdate = array();
      }
      $user=getSessionUser();
      $user->_arrayRevisionUpdate = array();
    }
    // End Download and install revision update //
  }
}

function cronSubscriptionUpdateVersion(){
  // Disconnect User and Application //
  $audit=new Audit();
  $list=$audit->getSqlElementsFromCriteria(array("idle"=>"0"));
  $result=i18n('colRequestDisconnection').'<input type="hidden" id="lastSaveId" value="" /><input type="hidden" id="lastOperation" value="update" /><input type="hidden" id="lastOperationStatus" value="NO_CHANGE" />';
  foreach($list as $audit) {
    if ($audit->sessionId!=session_id()) {
      $audit->requestDisconnection=1;
      $res=$audit->save();
      $userAudit=new User($audit->idUser);
      if ($userAudit->id) {
        $userAudit->cleanCookieHash();
        $userAudit->stopAllWork();
      }
    }
  }
  Parameter::storeGlobalParameter('applicationStatus', 'Close');
  Parameter::clearGlobalParameters();
  // End Disconnect User and Application //
  
  // Download and install version update //
  $jsonFile=RevisionUpdate::getRemoteFile("https://projeqtor.org/admin/getInstallableVersions.php");
  if ($jsonFile!==false) {
    $jsonList=json_decode($jsonFile,true);
    if(count($jsonList)!=0){
      ksort($jsonList);
      if($jsonList){
        $lastStable=-1;
        $lastRelease=-1;
        foreach ($jsonList as $key=>$val){
          $lastRelease=$key;
          if($val['stable']=='Y'){
            $lastStable=$key;
          }
        }
        $versionUpdateChannel = Parameter::getGlobalParameter('versionUpdateChannel');
        if(!pq_trim($versionUpdateChannel)){
          $versionUpdateChannel = 'stable';
        }
        $version = ($versionUpdateChannel == 'stable')?$lastStable:$lastRelease;
        if(isset($jsonList[$version])){
          $url=$jsonList[$version]['url'];
          if($url != ''){
            $file_path = "..".DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."version".DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR."projeqtor".$version.".zip";
            projeqtor_set_time_limit(0);
            ignore_user_abort(1);
            error_reporting(0);
            session_write_close();
            $fileInfo = RevisionUpdate::getFileInfo($url);
            if($fileInfo['http_code'] == 200){
              if(RevisionUpdate::fileDownload($url, $file_path)){
                enableCatchErrors();
                $renameResult=@rename($file_path, "..".DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."version".DIRECTORY_SEPARATOR."projeqtor".$version.".zip");
                disableCatchErrors();
                if ($renameResult) {
                  Sql::$maintenanceMode = true;
                  $oneFile="projeqtor".$version.".zip";
                  $files = RevisionUpdate::installAutoGetZipList ( $oneFile );
                  $result = "";
                  foreach ( $files as $file ) {
                    $result = RevisionUpdate::load ( $file );
                    traceLog($result);
                  }
                  $i18nSessionValue = 'i18nMessages' . ((isset ( $currentLocale )) ? $currentLocale : '');
                  unsetSessionValue ( $i18nSessionValue, false );
                } else {
                  errorLog("file transfer issue : cannot rename ".$file_path. ' into '."..".DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."version".DIRECTORY_SEPARATOR."projeqtor".$version.".zip");
                  errorLog("Check access rights to target folder");
                }
              }else{
                errorLog("file transfer issue : download not complete");
                kill ($file_path);
              }
            } else {
              errorLog("file transfer issue : return code ".$fileInfo['http_code']." instead of 200");
              errorLog($fileInfo);
            }
          }else{
            errorLog("file transfer issue : cannot get correct url");
          }
        }else{
          errorLog("Version update : no version find to download");
        }
      }
    }
  }
  // End Download and install version update //
}

function cronReorderWBS(){
  traceLog("=== Automatic reorder of Projects from Priority === Start ");
  $planningElement = new PlanningElement();
  $result=$planningElement->renumberWbs(false, true);
  traceLog("Total projects with changed WBS: " . $result);
  traceLog("=== Automatic reorder of Projects from Priority === End ");
}

?>
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
 * Save a situation : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

// Get the situation info
$prospectId=RequestHandler::getId('prospectId');
$eventId=RequestHandler::getId('eventId');
$name=RequestHandler::getValue('eventName');
$prospectEventType=RequestHandler::getId('prospectEventType');
$description=RequestHandler::getValue('eventDescription');
$date = RequestHandler::getValue('eventDate');
$time = RequestHandler::getValue('eventTime');
$dateTime = $date.' '.pq_substr($time, 1);
$eventId=pq_trim($eventId);
$action=RequestHandler::getValue('action');
if ($eventId=='') {
  $eventId=null;
}

Sql::beginTransaction();
if ($eventId) {
	$event=new ProspectEvent($eventId);
} else {
	$event=new ProspectEvent();
}

if($action == 'remove'){
  $result=$event->delete();
}else{
  $event->idProspect = $prospectId;
  if (!$event->idUser) $event->idUser=getCurrentUserId();
  $event->eventDateTime = $dateTime;
  $event->name=$name;
  $event->idProspectEventType=$prospectEventType;
  $event->description=$description;
  $event->idle=0;  
  $result=$event->save();
}
// Message of correct saving
displayLastOperationStatus($result);

// Update last date
$max=$event->getMaxValueFromCriteria('eventDateTime', array('idProspect'=>$prospectId));
$p=new Prospect($prospectId);
$p->lastEventDatetime=$max;
$p->save();
?>
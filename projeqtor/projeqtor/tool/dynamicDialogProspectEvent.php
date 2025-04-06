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
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";

$objectClass=RequestHandler::getClass('objectClass',true);
$objectId=RequestHandler::getId('objectId');
$eventId=RequestHandler::getId('eventId');

$object = new $objectClass($objectId);

if ($eventId) {
	$event=new ProspectEvent($eventId);
} else {
	$event=new ProspectEvent();
	$event->idProspect=$objectId;
}

$detailHeight=600;
$detailWidth=1010;
if (sessionValueExists('screenWidth') and getSessionValue('screenWidth')) {
  $detailWidth = round(getSessionValue('screenWidth') * 0.60);
}
if (sessionValueExists('screenHeight')) {
  $detailHeight=round(getSessionValue('screenHeight')*0.60);
}
if($event->eventDateTime){
  $dateTime = pq_explode(' ', $event->eventDateTime);
  $date = $dateTime[0];
  $time = $dateTime[1];
}else{
  $date = date('Y-m-d');
  $time = date('H:i:s');
}
$userId = getCurrentUserId();
?>
<div style="width:800px">
  <form dojoType="dijit.form.Form" id='prospectEventForm' name='prospectEventForm' onSubmit="return false;" >
    <input id="prospectId" name="prospectId" type="hidden" value="<?php echo $event->idProspect;?>" />
    <input id="eventId" name="eventId" type="hidden" value="<?php echo $event->id;?>" />
    <table style="width:100%;">
      <tr>
        <td style="vertical-align:middle; width:200px">
          <label class="dialogLabel" for="eventName"><?php echo i18n('colName');?>&nbsp;<?php if (!isNewGui()) echo ': ';?></label>
        </td>
        <td>
          <input id="eventName" name="eventName" value="<?php echo $event->name;?>" 
                 dojoType="dijit.form.TextBox" class="input required" required='required' style="width:600px" />
        </td>
      </tr>
      <tr>
        <td>
          <label class="dialogLabel" for="prospectEventType"><?php echo i18n('colType');?>&nbsp;<?php if (!isNewGui()) echo ': ';?></label>
        </td>
        <td>
          <select dojoType="dijit.form.FilteringSelect" class="input required" required='required'
            style="width: 300px;" name="prospectEventType" id="prospectEventType"
            <?php echo autoOpenFilteringSelect();?> value="<?php echo $event->idProspectEventType;?>">
              <?php
               htmlDrawOptionForReference('idProspectEventType', $event->idProspectEventType);
               ?>  
          </select>
        </td>
      </tr>
      <tr style="height:16px">
        <td>
          <label class="dialogLabel" for="eventDate" style="text-align:right"><?php echo i18n('colDate');?>&nbsp;<?php if (!isNewGui()) echo ': ';?></label>
        </td>
        <td>
          <div id="eventDate" name="eventDate" dojoType="dijit.form.DateTextBox" invalidMessage="<?php echo i18n('messageInvalidDate'); ?>" type="text" maxlength="10"
          <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
            echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
          }?>
          style="width:82px;text-align: center;margin-right:-3px;<?php echo (isNewGui())?'':'margin-top:1px;';?>" 
          class="input required generalColClass" required='required' value="<?php echo $date;?>" hasDownArrow="false" >
          </div>
          <div id="eventTime" name="eventTime" dojoType="dijit.form.TimeTextBox" invalidMessage="<?php echo i18n('messageInvalidTime'); ?>" type="text" maxlength="8"
          <?php if (sessionValueExists('browserLocaleTimeFormat')) {
            echo ' constraints="{timePattern:\''.getSessionValue('browserLocaleTimeFormat').'\'}" ';
          }?>
          style="width:64px;text-align: center;" class="input required generalColClass" required='required' value="T<?php echo $time;?>" hasDownArrow="false" >
          </div>
        <td>
      </tr>
      <tr>
        <td colspan="2">
          <label class="tabLabel" for="eventDescription" style="text-align:left;font-weight:normal; width:300px;<?php echo (isNewGui())?'position:relative;top:-6px;background:transparent':'';?>"><?php echo i18n('colDescription');?></label><br/>
          <div style="width:800px;">
            <textarea style="width:800px; height:<?php echo $detailHeight;?>px" name="eventDescription" id="eventDescription" ><?php
              if (!isTextFieldHtmlFormatted($event->description)) {
              	echo formatPlainTextForHtmlEditing($event->description);
              } else {
              	echo pq_htmlspecialchars($event->description);
              } ?></textarea>
          </div>
        </td>
      </tr>
    </table>
  </form>
  <table style="width:100%">
    <tr>
      <td align="center">
        <input type="hidden" id="dialogeventAction">
        <button class="mediumTextButton"  dojoType="dijit.form.Button" type="button" onclick="formInitialize();dijit.byId('dialogProspectEvent').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton"  id="dialogProspectEventSubmit" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);formInitialize();saveProspectEvent();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>
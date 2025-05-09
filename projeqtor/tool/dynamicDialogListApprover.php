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

if (! array_key_exists('objectId',$_REQUEST)) {
	throwError('Parameter objectId not found in REQUEST');
}
$objectId=$_REQUEST['objectId'];

$approver = new Approver();
$crit = array('refType'=>"DocumentVersion",'refId'=>$objectId);
$lstApp = $approver->getSqlElementsFromCriteria($crit,false);
echo '<table style="width:100%;">';
echo '<tr>';
echo '<td class="historyHeader" style="width:100px">' . i18n('colName') . '</td>';
echo '<td class="historyHeader" style="width:100px">' . i18n('colApproved') . '</td>';
echo '<td class="historyHeader" style="width:100px">' . i18n('colDate') . '</td>';
echo '<td class="historyHeader" style="width:300px;">' . i18n('colRejectMotif') . '</td>';
echo '</tr>';

foreach ($lstApp as $lstApps){
  $user = SqlList::getNameFromId('Affectable', $lstApps->idAffectable); 
  echo '<td class="historyData">' . $user . '</td>';
  if($lstApps->approved){
    echo '<td class="historyData" style="text-align:center;"><img src="../view/img/check.png" width="12" height="12" /></td>';  
  }else if($lstApps->disapproved){
    echo '<td class="historyData" style="text-align:center;"><img src="../view/img/uncheck.png" width="12" height="12" /></td>';
  }else {
    echo '<td class="historyData" ></td>';
  }
  $date = $lstApps->approvedDate;
  if($lstApps->disapproved){
    $date = $lstApps->disapprovedDate;
  }
  $date = htmlFormatDateTime($date);
  echo '<td class="historyData" style="text-align:center;">' . $date . '</td>';
  echo '<td class="historyData" style="text-align:left;">' . pq_nl2br($lstApps->disapprovedComment) . '</td></tr>';
}

?>
<table style="margin:auto">
  <tr>
    <td>
      <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogListApprover').hide();">
        <?php echo pq_ucfirst(i18n("closed"));?>
      </button>
    </td>
  </tr>
</table>

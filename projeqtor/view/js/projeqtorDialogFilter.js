///*******************************************************************************
// * COPYRIGHT NOTICE *
// * 
// * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org Contributors : -
// * 
// * This file is part of ProjeQtOr.
// * 
// * ProjeQtOr is free software: you can redistribute it and/or modify it under
// * the terms of the GNU Affero General Public License as published by the Free Software
// * Foundation, either version 3 of the License, or (at your option) any later
// * version.
// * 
// * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
// * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
// * A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
// * 
// * You should have received a copy of the GNU Affero General Public License along with
// * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
// * 
// * You can get complete code of ProjeQtOr, other resource, help and information
// * about contributors at http://www.projeqtor.org
// * 
// * DO NOT REMOVE THIS NOTICE **
// ******************************************************************************/
//
//// ============================================================================
//// All specific ProjeQtOr functions and variables for Dialog Purpose
//// This file is included in the main.php page, to be reachable in every context
//// ============================================================================

// =============================================================================
// = Filter
// =============================================================================

var filterStartInput=false;
var filterFromDetail=false;
function showFilterDialog() {
  function callBack() {
    filterStartInput=false;
    window.top.filterFromDetail=false;

    dojo.style(dijit.byId('idFilterOperator').domNode,{
      visibility:'hidden'
    });
    dojo.style(dijit.byId('filterValue').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueList').domNode,{
      display:'none'
    });
    if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dojo.style(dijit.byId('showDetailInFilter').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueCheckbox').domNode,{
      display:'none'
    });
    if (dijit.byId('filterValueCheckboxSwitch')) {
      dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode,{
        display:'none'
      });
    }
    dojo.style(dijit.byId('filterValueDate').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
      display:'none'
    });
    
    dojo.byId('filterDynamicParameterPane').style.display='none';
    dojo.byId('filterCompareParameterPane').style.display='none';
    
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dijit.byId('idFilterAttribute').reset();
    if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) dojo.byId('filterObjectClass').value=dojo.byId('objectClassList').value;
    else if (dojo.byId('objectClassManual') && dojo.byId('objectClassManual').value) dojo.byId('filterObjectClass').value=dojo.byId('objectClassManual').value;
    else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) dojo.byId('filterObjectClass').value=dojo.byId('objectClass').value;
    else dojo.byId('filterObjectClass').value=null;
    filterType="";
    var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
    dojo.xhrPost({
      url:"../tool/backupFilter.php?filterObjectClass=" + dojo.byId('filterObjectClass').value + compUrl +addTokenIndexToUrl(),
      handleAs:"text",
      load:function(data,args) {
      }
    });
    compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
    loadContent("../tool/displayFilterClause.php" + compUrl,"listFilterClauses","dialogFilterForm",false,null,null,null);
    loadContent("../tool/refreshFilterNameLayout.php" + compUrl,"filterNameLayoutDiv","dialogFilterForm",false, null, null, null);
    loadContent("../tool/displayFilterList.php" + compUrl,"listStoredFilters","dialogFilterForm",false);
    loadContent("../tool/displayFilterSharedList.php" + compUrl,"listSharedFilters","dialogFilterForm",false);
    const filterData = document.querySelectorAll('td[id^="filterData_"]');
    filterData.forEach(function(td) {
      if (td.dataset.selected === 'isSelected') {
        const idsPart = td.id.replace('filterData_', '');
        const [filterId, idLayout] = idsPart.split('#');
        selectStoredFilter(filterId,idLayout,'','','');
      }
    });
    var objectClass='';
    if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) objectClass=dojo.byId('objectClassList').value;
    else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value
        && (dojo.byId("objectClassManual").value == 'Planning' || dojo.byId("objectClassManual").value == 'PlanningWorkPlan' || dojo.byId("objectClassManual").value == 'VersionsPlanning' || dojo.byId("objectClassManual").value == 'ResourcePlanning')) objectClass='Activity';
    else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) objectClass=dojo.byId('objectClass').value;
    if (objectClass.substr(0,7) == 'Report_') objectClass=objectClass.substr(7);
    refreshListSpecific('object','idFilterAttribute','objectClass',objectClass);
    dijit.byId("dialogFilter").show();
  }
  loadDialog('dialogFilter',callBack,true,"",true);
}

function displayOrOperator() {
  if (dojo.byId('nbFilterCriteria').value != "0") {
    dojo.byId('filterLogicalOperator').style.display='block';
  }
}
var samefilteroperator = null;
function filterSelectAtribute(value) {
  if (value) {
    filterStartInput=true;
    if (dijit.byId('filterCompareParameterSwitch')) dijit.byId('filterCompareParameterSwitch').set('value','off');
    if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value','off');
    if (editFilterValueDynamic!==null && editFilterValueDynamic==1){
      dijit.byId("filterDynamicParameterSwitch").set("value",'on');
    }
    var dataTypeEdit=null;
    dijit.byId('idFilterAttribute').store.store.fetchItemByIdentity({
      identity:value,
      onItem:function(item) {
        var dataType=dijit.byId('idFilterAttribute').store.store.getValue(item,"dataType","inconnu");
        if (value == "refTypeIncome" || value == "refTypeExpense") {
          dataType="list";
        }
        var datastoreOperator=new dojo.data.ItemFileReadStore({
          url:'../tool/jsonList.php?listType=operator&dataType=' + dataType + ''+addTokenIndexToUrl()
        });
        var storeOperator=new dojo.store.DataStore({
          store:datastoreOperator
        });
        storeOperator.query({
          id:"*"
        });
        dijit.byId('idFilterOperator').set('store',storeOperator);
        datastoreOperator.fetch({
          query:{
            id:"*"
          },
          count:1,
          onItem:function(item) {
            var filteroperator = (editFilterOperator )? editFilterOperator : item.id;
            if (editFilterOperator!==null){
              if (editFilterOperator=='BETWEEN'){
                filteroperator = '=';
              }else if(editFilterOperator=='NOT BETWEEN'){
                filteroperator = '<>';
              }else if(editFilterOperator=='is null'){
                filteroperator = 'isEmpty';
              }else if(editFilterOperator=='is not null'){
                filteroperator = 'isNotEmpty';
              }else if(editFilterOperator=='LIKE' && editFilterOperatorDisp && editFilterOperatorDisp=='startBy'){
                filteroperator = 'startBy'; 
              }else if(editFilterOperator=='<='){
                if (editFilterValue.toUpperCase().includes('ADDDATE')){
                  filteroperator = '<=now+';
                }
              }else if(editFilterOperator=='>='){
                if (editFilterValue.toUpperCase().includes('ADDDATE')){
                  filteroperator = '>=now+';
                }
              }
              if (samefilteroperator==filteroperator) filterSelectOperator(filteroperator); 
                samefilteroperator=  filteroperator; 
            }
            dijit.byId('idFilterOperator').set("value",filteroperator);
          },
          onError:function(err) {
            console.info(err.message);
          }
        });
        dojo.style(dijit.byId('idFilterOperator').domNode,{
          visibility:'visible'
        });
        var datastoreCompare=new dojo.data.ItemFileReadStore({
          url:'../tool/jsonList.php?listType=empty&attribute='+value+'&dataType=' + dataType + ''+addTokenIndexToUrl()
        });
        var storeCompare=new dojo.store.DataStore({
          store:datastoreCompare
        });
        storeCompare.query({
          id:"*"
        });
        dijit.byId('FilterCompareAttribute').set('store',storeCompare);
        datastoreCompare.fetch({
          query:{
            id:"*"
          },
          count:1,
          onItem:function(item) {
            dijit.byId('FilterCompareAttribute').set("value",item.id);
          },
          onError:function(err) {
            console.info(err.message);
          }
        });
        dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
          visibility:'visible'
        });
        // ADD qCazelles - Dynamic filter - Ticket #78
        if (editFilterValueCompare) {
          dojo.byId('filterDynamicParameterPane').style.display = 'none';
        }
        else dojo.byId('filterDynamicParameterPane').style.display='block';
        dojo.byId('filterCompareParameterPane').style.display='none';
		dijit.byId("filterCompareParameter").set("checked",false);
		
        if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
        // END ADD qCazelles - Dynamic filter - Ticket #78
        dojo.byId('filterDataType').value=dataType;
        if (dataType == "bool") {
          dataTypeEdit="bool";
          dijit.byId("filterCompareParameter").set("checked",false);
          dojo.byId('filterCompareParameterPane').style.display='none';
          filterType="bool";
          dojo.style(dijit.byId('filterValue').domNode,{
            display:'none'
          });
          dojo.style(dijit.byId('filterValueList').domNode,{
            display:'none'
          });
          if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
          if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
          if (dijit.byId('filterValueCheckboxSwitch')) {
            dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode,{
              display:'block'
            });

            if (isNewGui) dojo.byId("filterValueCheckboxSwitch").style.top="10px";
            dijit.byId('filterValueCheckbox').set('value','off');
          } else {
            dojo.style(dijit.byId('filterValueCheckbox').domNode,{
              display:'block'
            });
            dijit.byId('filterValueCheckbox').set('checked','');
          }
          dojo.style(dijit.byId('filterValueDate').domNode,{
            display:'none'
          });
          dojo.style(dijit.byId('showDetailInFilter').domNode,{
            display:'none'
          });
        } else if (dataType == "list") {
          dataTypeEdit='list';
          dijit.byId("filterCompareParameter").set("checked",false);
          dojo.byId('filterCompareParameterPane').style.display='none';
          filterType="list";
          var extraUrl="";
          if (value == 'idTargetVersion' || value == 'idTargetProductVersion' || value == 'idOriginalProductVersion') {
            value='idProductVersion';
            if (dojo.byId('archiveOn') && dojo.byId('archiveOn').style.display=='none'){
              extraUrl='&critField=idle&critValue=0'; 
            }else{
              extraUrl='&critField=idle&critValue=all';
            }
          } else if (value == 'idTargetComponentVersion' || value == 'idOriginalComponentVersion') {
            value='idComponentVersion';
            if (dojo.byId('archiveOn') && dojo.byId('archiveOn').style.display=='none'){
              extraUrl='&critField=idle&critValue=0'; 
            }else{
              extraUrl='&critField=idle&critValue=all';
            }
          } else if (value == 'idResourceSelect' || (value == 'idResource' && dojo.byId('filterObjectClass') && dojo.byId('filterObjectClass').value=='Assignment' )) {
            value='idResourceAllNoMaterial';
          }
          var urlListFilter='../tool/jsonList.php?required=true&listType=list&dataType=' + value + ''+addTokenIndexToUrl();

          // CHANGE qCazelles - Ticket 165 //Empty lists on filter in
          // comboDetail
          // Old
          // if (currentSelectedProject && currentSelectedProject!='' &&
          // currentSelectedProject!='*') {
          // New
          if (typeof currentSelectedProject != 'undefined' && currentSelectedProject != '' && currentSelectedProject != '*') {
            // END CHANGE qCazelles - Ticket 165
            if (value == 'idActivity') {
              urlListFilter+='&critField=idProjectSub&critValue=' + currentSelectedProject;
            }
            if (value.substr(0,2)=='id' && value.substr(-4)=='Type') {
              // noting              
            } else if (value == 'idComponent') {
              // noting
            } else if (value=='idProject') {
              urlListFilter+='&critField=id&critValue=' + currentSelectedProject;
            } else {
              urlListFilter+='&critField=idProject&critValue=' + currentSelectedProject;
            }
            if (extraUrl == '&critField=idle&critValue=all') {
              extraUrl == '&critField1=idle&critValue1=all';
            }
          }
          if (extraUrl != "") {
            urlListFilter+=extraUrl;
          }
          var tmpStore=new dojo.data.ItemFileReadStore({
            url:urlListFilter + addTokenIndexToUrl(urlListFilter)
          });
          var mySelect=dojo.byId("filterValueList");
          mySelect.options.length=0;
          var nbVal=0;
          // ADD aGaye - Ticket 196
          if (dijit.byId('idFilterAttribute').getValue() == "idBusinessFeature") {
            var listId="";
            tmpStore.fetch({
              query:{
                id:"*"
              },
              onItem:function(item) {
                listId+=(listId != "") ? '_' : '';
                listId+=parseInt(tmpStore.getValue(item,"id",""),10) + '';
                nbVal++;
              },
              onError:function(err) {
                console.info(err.message);
              },
              onComplete:function() {
                dojo.xhrGet({
                  url:'../tool/getProductNameFromBusinessFeature.php?listId=' + listId + ''+addTokenIndexToUrl(),
                  handleAs:"text",
                  load:function(data) {
                    var listName=JSON.parse(data);
                    tmpStore.fetch({
                      query:{
                        id:"*"
                      },
                      onItem:function(item) {
                        mySelect.options[mySelect.length]=new Option(tmpStore.getValue(item,"name","") + " (" + listName[tmpStore.getValue(item,"id","")] + ")",tmpStore.getValue(item,"id",""));
                      },
                      onError:function(err) {
                        console.info(err.message);
                      }
                    });
                  }
                });
              }
            });
          } else {
            valueSelect=[];
            if (editFilterValue!==null) {
              editFilterValue=editFilterValue.replace(/\(|\)|\s/g,'');
              valueSelect=editFilterValue.split(',');
            }
            tmpStore.fetch({
              query:{
                id:"*"
              },
              onItem:function(item) {
                selected=(valueSelect.indexOf(item.id[0])!=-1)?true:false;
                mySelect.options[mySelect.length]=new Option(tmpStore.getValue(item,"name",""),tmpStore.getValue(item,"id",""),selected,selected);
                nbVal++;
              },
              onError:function(err) {
                console.info(err.message);
              }
            });
          }
          // END aGaye - Ticket 196
          mySelect.size=(nbVal > 10) ? 10 : nbVal;
          dojo.style(dijit.byId('filterValue').domNode,{
            display:'none'
          });
          dojo.style(dijit.byId('filterValueList').domNode,{
            display:'block'
          });
            if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="8px";
            if (isNewGui) dojo.byId("filterValueListHideTop").style.display="block";
          dojo.xhrGet({
            url : "../tool/checkAccessForScreen.php?listType="+value+addTokenIndexToUrl(),
            handleAs : "text",
            load : function(data) {
              if(data && data=="YES"){          
                dojo.style(dijit.byId('showDetailInFilter').domNode, {display : 'block'}); 
              } else {
                dojo.style(dijit.byId('showDetailInFilter').domNode, {display : 'none'});
              }
            }
          });
          dijit.byId('showDetailInFilter').set('value',item.id);
          dijit.byId('filterValueList').reset();
          dojo.style(dijit.byId('filterValueCheckbox').domNode,{
            display:'none'
          });
          if (dijit.byId('filterValueCheckboxSwitch')) {
            dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode,{
              display:'none'
            });
          }
          dojo.style(dijit.byId('filterValueDate').domNode,{
            display:'none'
          });
        } else if (dataType == "date") {
          dataTypeEdit='date';
          filterType="date";
          dojo.byId('filterCompareParameterPane').style.display='block';
          dojo.style(dijit.byId('filterValue').domNode,{
            display:'none'
          });
          dojo.style(dijit.byId('filterValueList').domNode,{
            display:'none'
          });
          if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
          if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
          dojo.style(dijit.byId('showDetailInFilter').domNode,{
            display:'none'
          });
          dojo.style(dijit.byId('filterValueCheckbox').domNode,{
            display:'none'
          });
          if (dijit.byId('filterValueCheckboxSwitch')) {
            dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode,{
              display:'none'
            });
          }
          dojo.style(dijit.byId('filterValueDate').domNode,{
            display:'block'
          });
          dijit.byId('filterValueDate').reset();
        } else {
    		  if (dataType=='decimal') {
    		    dataTypeEdit='decimal';
    		    if (isNewGui) dojo.byId("filterCompareParameterPane").style.left="200px";
    	      if (isNewGui) dojo.byId("filterCompareParameterPane").style.top="30px";
    	      if (isNewGui) dojo.byId('filterCompareParameterPane').style.display='block';
    		  } else {
    		    dataTypeEdit='varchar';
    		    dojo.byId('filterCompareParameterPane').style.display='none';
    		    dijit.byId("filterCompareParameter").set("checked",false);
    		  }
          filterType="text";
          dojo.style(dijit.byId('filterValue').domNode,{
            display:'block'
          });
          dijit.byId('filterValue').reset();
          dojo.style(dijit.byId('filterValueList').domNode,{
            display:'none'
          });
          if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
          if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
          dojo.style(dijit.byId('showDetailInFilter').domNode,{
            display:'none'
          });
          dojo.style(dijit.byId('filterValueCheckbox').domNode,{
            display:'none'
          });
          if (dijit.byId('filterValueCheckboxSwitch')) {
            dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode,{
              display:'none'
            });
          }
          dojo.style(dijit.byId('filterValueDate').domNode,{
            display:'none'
          });
        }
      },
      onError:function(err) {
        dojo.style(dijit.byId('idFilterOperator').domNode,{
          visibility:'hidden'
        });
        dojo.style(dijit.byId('filterValue').domNode,{
          display:'none'
        });
        dojo.style(dijit.byId('filterValueList').domNode,{
          display:'none'
        });
        if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
        if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
        dojo.style(dijit.byId('showDetailInFilter').domNode,{
          display:'none'
        });
        dojo.style(dijit.byId('filterValueCheckbox').domNode,{
          display:'none'
        });
        if (dijit.byId('filterValueCheckboxSwitch')) {
          dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode,{
            display:'none'
          });
        }
        dojo.style(dijit.byId('filterValueDate').domNode,{
          display:'none'
        });
        // hideWait();
      }
    });
    dijit.byId('filterValue').reset();
    dijit.byId('filterValueList').reset();
    dijit.byId('filterValueCheckbox').reset();
    if (dijit.byId('filterValueCheckboxSwitch')) {
      dijit.byId('filterValueCheckboxSwitch').reset();
    }
    dijit.byId('filterValueDate').reset();
    dojo.byId('buttonSaveFieldEdition').style.display=(editFilterValue!==null || editFilterValueDynamic!==null)?'block':'none';
    
    if (editFilterValue!==null) {
      if (dataTypeEdit == "bool" && editFilterValue==1){
        dijit.byId('filterValueCheckboxSwitch').set('value','on');
      }else if (dataTypeEdit == "date"){
        var dateFilter = editFilterValue.match(/(\d{4}-\d{2}-\d{2})/);
        if (dateFilter) dijit.byId('filterValueDate').set('value', new Date(dateFilter[1]));
        if (editFilterOperator=='<=' || editFilterOperator=='>='){
          var valueFilter = editFilterValueDisp.match(/(\d+)/);
          if (valueFilter) dijit.byId("filterValue").set("value",valueFilter[1]);
        } 
      }else if (dataTypeEdit!='list'){
        valueFilter = editFilterValue;
        if (editFilterOperator=='LIKE' || editFilterOperator=='NOT LIKE'){
          valueFilter =  editFilterValueDisp.replace(/^'|'$/g, '');
        }
        dijit.byId("filterValue").set("value",valueFilter);
      }
      if (editFilterOperator!='NOT IN' && editFilterOperator!='IN' ){
        dojo.byId('showDetailInFilter').style.display='none'; 
      }else{
        dojo.byId('showDetailInFilter').style.display='block';
      }
    }
    //#6012
    var objectClass='';
    if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) objectClass=dojo.byId('objectClassList').value;
    else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value
        && (dojo.byId("objectClassManual").value == 'Planning' || dojo.byId("objectClassManual").value == 'PlanningWorkPlan' || dojo.byId("objectClassManual").value == 'VersionsPlanning' || dojo.byId("objectClassManual").value == 'ResourcePlanning')) objectClass='Activity';
    else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) objectClass=dojo.byId('objectClass').value;
    if (objectClass.substr(0,7) == 'Report_') objectClass=objectClass.substr(7);
    if (dijit.byId('filterCompareParameter').get('checked')) {
      dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
        display:'block'
      });
      if (isNewGui) dojo.byId("filterCompareParameterPane").style.left="200px";
      if (isNewGui) dojo.byId("filterCompareParameterPane").style.top=(dojo.byId("filterDynamicParameterPane").style.display=='none')?"9px":"30px";
      dojo.style(dijit.byId('filterValue').domNode,{
        display:'none'
      });
      dojo.style(dijit.byId('filterValueList').domNode,{
        display:'none'
      });
      dojo.style(dijit.byId('filterValueCheckbox').domNode,{
        display:'none'
      });
      dojo.style(dijit.byId('filterValueDate').domNode,{
        display:'none'
      });
      dojo.style(dijit.byId('filterSortValueList').domNode,{
        display:'none'
      });
      dijit.byId('FilterCompareAttribute').set('value', null);
      refreshListSpecific('object','FilterCompareAttribute','objectClass',objectClass, null, false, 'field', value);
    }else{
      dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
        display:'none'
      }); 
    }    //#6012
  } else {
    dojo.style(dijit.byId('idFilterOperator').domNode,{
      visibility:'hidden'
    });
    dojo.style(dijit.byId('filterValue').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueList').domNode,{
      display:'none'
    });
    if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dojo.style(dijit.byId('showDetailInFilter').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueCheckbox').domNode,{
      display:'none'
    });
    if (dijit.byId('filterValueCheckboxSwitch')) {
      dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode,{
        display:'none'
      });
    }
    dojo.style(dijit.byId('filterValueDate').domNode,{
      display:'none'
    });
  }
}

function filterActiveCompareField() {
  var valueFilterAttribute = dijit.byId('idFilterAttribute').get('value');
  var valueFilterOperator = dijit.byId('idFilterOperator').get('value');
  var dataType = dojo.byId('filterDataType').value;
  var objectClass='';
  if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) objectClass=dojo.byId('objectClassList').value;
  else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value
      && (dojo.byId("objectClassManual").value == 'Planning' || dojo.byId("objectClassManual").value == 'PlanningWorkPlan' || dojo.byId("objectClassManual").value == 'VersionsPlanning' || dojo.byId("objectClassManual").value == 'ResourcePlanning')) objectClass='Activity';
  else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) objectClass=dojo.byId('objectClass').value;
  if (objectClass.substr(0,7) == 'Report_') objectClass=objectClass.substr(7);
  if (dijit.byId('filterCompareParameter').get('checked')) {
    dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
      display:'block'
    });
    if (isNewGui) dojo.byId("filterCompareParameterPane").style.left="200px";
    if (isNewGui) dojo.byId("filterCompareParameterPane").style.top=(dojo.byId("filterDynamicParameterPane").style.display=='none')?"9px":"30px";
    dojo.style(dijit.byId('filterValue').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueList').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueCheckbox').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueDate').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterSortValueList').domNode,{
      display:'none'
    });
    dijit.byId('FilterCompareAttribute').set('value', null);
    refreshListSpecific('object','FilterCompareAttribute','objectClass',objectClass, null, false, 'field', valueFilterAttribute);
    if (editFilterValue!==null) {
      editFilterValue=editFilterValue.replace(/\[|\]/g, '');
     dijit.byId('FilterCompareAttribute').set('value',editFilterValue);
    }
  }else{
    dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
      display:'none'
    }); 
    if(dataType == 'List'){
      dojo.style(dijit.byId('filterValueList').domNode,{
        display:'block'
      });
    } else if (dataType == 'date'){
      if(valueFilterOperator == '<=now+'){
        dojo.style(dijit.byId('filterValueDate').domNode,{
          display:'none'
        });
      }else{
        dojo.style(dijit.byId('filterValueDate').domNode,{
          display:'block'
        });
      }
    }else if(dataType == 'bool'){
      dojo.style(dijit.byId('filterValueCheckbox').domNode,{
        display:'block'
      });
    }else{
      dojo.style(dijit.byId('filterValue').domNode,{
        display:'block'
      });
    }
  }    //#6012
}

function filterSelectOperator(operator) {
  filterStartInput=true;
  if (dijit.byId('filterCompareParameterSwitch')) dijit.byId('filterCompareParameterSwitch').set('value','off');
  dojo.byId('filterCompareParameterPane').style.display='block';
  if (operator == "SORT") {
    filterType="SORT";
    dojo.style(dijit.byId('filterValue').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueList').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
      display:'none'
    })
    dojo.byId('filterCompareParameterPane').style.display='none';
    dijit.byId("filterCompareParameter").set("checked",false);
    if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dojo.style(dijit.byId('showDetailInFilter').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueCheckbox').domNode,{
      display:'none'
    });
    if (dijit.byId('filterValueCheckboxSwitch')) {
      dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode,{
        display:'none'
      });
    }
    dojo.style(dijit.byId('filterValueDate').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterSortValueList').domNode,{
      display:'block'
    });
    dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
      display:'none'
    });

    dijit.byId('filterDynamicParameter').set('checked','');
    if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value','off');
    dojo.byId('filterDynamicParameterPane').style.display='none';
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    if (dijit.byId('filterCompareParameterSwitch')) dijit.byId('filterCompareParameterSwitch').set('value','off');
    
  } else if (operator == "<=now+" || operator == ">=now+") {
    if (dijit.byId('filterCompareParameter').get('checked')) {
      dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
        display:'block'
      });
      if (isNewGui) dojo.byId("filterCompareParameterPane").style.left="200px";
      if (isNewGui) dojo.byId("filterCompareParameterPane").style.top=(dojo.byId("filterDynamicParameterPane").style.display=='none')?"9px":"30px";
      dojo.style(dijit.byId('filterValue').domNode,{
        display:'none'
      });
      
    } else {
    filterType="text";
    dojo.style(dijit.byId('filterValue').domNode,{
      display:'block'
    });
    dojo.style(dijit.byId('filterValueList').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueDate').domNode,{
      display:'none'
    });
    if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dojo.style(dijit.byId('showDetailInFilter').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueCheckbox').domNode,{
      display:'none'
    });
    if (dijit.byId('filterValueCheckboxSwitch')) {
      dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode,{
        display:'none'
      });
    }
    dojo.style(dijit.byId('filterSortValueList').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
      display:'none'
    });
    }
    
  } else if (operator == "isEmpty" || operator == "isNotEmpty" || operator == "hasSome") {
    filterType="null";
    dojo.style(dijit.byId('filterValue').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueList').domNode,{
      display:'none'
    });
    if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dojo.style(dijit.byId('showDetailInFilter').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterValueCheckbox').domNode,{
      display:'none'
    });
    if (dijit.byId('filterValueCheckboxSwitch')) {
      dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode,{
        display:'none'
      });
    }
    dojo.style(dijit.byId('filterValueDate').domNode,{
      display:'none'
    });
    dojo.style(dijit.byId('filterSortValueList').domNode,{
      display:'none'
    });
    dijit.byId('filterDynamicParameter').set('checked','');
    if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value','off');
    dojo.byId('filterDynamicParameterPane').style.display='none';
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    if (dijit.byId('filterCompareParameterSwitch')) dijit.byId('filterCompareParameterSwitch').set('value','off');
    dojo.byId('filterCompareParameterPane').style.display='none';
    if (dijit.byId('filterCompareParameter').get('checked')) {
      dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
        display:'none'
      });
      dojo.style(dijit.byId('filterCompareParameter').domNode,{
        display:'none'
      });
    }
  } else {
    dojo.style(dijit.byId('filterValue').domNode,{
      display:'none'
    });
    dataType=dojo.byId('filterDataType').value;
    dojo.style(dijit.byId('filterSortValueList').domNode,{
      display:'none'
    });
    if (dataType == "bool") {
      dojo.byId('filterCompareParameterPane').style.display='none';
      filterType="bool";
      if (dijit.byId('filterValueCheckboxSwitch')) {
        dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode,{
          display:'block'
        });
      } else {
        dojo.style(dijit.byId('filterValueCheckbox').domNode,{
          display:'block'
        });
      }
    } else if (dataType == "list") {
      filterType="list";
      dijit.byId("filterCompareParameter").set("checked",false);
      dojo.byId('filterCompareParameterPane').style.display='none';
//      if(dijit.byId('filterCompareParameter').get('checked')) {
//        dojo.style(dijit.byId('filterValueList').domNode,{
//          display:'none'
//        });
//        dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
//          display:'block' 
//        });
//        dojo.style(dijit.byId('showDetailInFilter').domNode,{
//          display:'block'
//        });
//        dijit.byId('filterDynamicParameter').set('checked','');
//        if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value','off');
//        dojo.byId('filterDynamicParameterPane').style.display='block';
//        if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
//        //if (isNewGui) dojo.byId("filterCompareParameterPane").style.left="200px";
//        //if (isNewGui) dojo.byId("filterCompareParameterPane").style.top="30px";
//        if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
//      }else{
        if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="8px";
        if (isNewGui) dojo.byId("filterValueListHideTop").style.display="block";
        //if (isNewGui) dojo.byId("filterCompareParameterPane").style.left="180px";
        //if (isNewGui) dojo.byId("filterCompareParameterPane").style.top="9px";
        //if(!dijit.byId('filterCompareParameter').get('checked')) {
        //  dojo.byId('filterCompareParameterPane').style.display='none';
        //}
 //     }
    } else if (dataType == "date") {
      filterType="date";
      dojo.style(dijit.byId('filterValueDate').domNode,{
        display:'block'
      });
      dijit.byId('filterDynamicParameter').set('checked','');
      if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value','off');
      if (editFilterValueCompare) dojo.byId('filterDynamicParameterPane').style.display = 'none';
      else dojo.byId('filterDynamicParameterPane').style.display='block';
      if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    } else {
      filterType="text";
      dojo.style(dijit.byId('filterValue').domNode,{
        display:'block'
      });
      dijit.byId('filterDynamicParameter').set('checked','');
      if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value','off');
      if (editFilterValueCompare) dojo.byId('filterDynamicParameterPane').style.display = 'none';
      else dojo.byId('filterDynamicParameterPane').style.display='block';
      if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none"; 
    } 
    //#6012
    var objectClass='';
    if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) objectClass=dojo.byId('objectClassList').value;
    else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value
        && (dojo.byId("objectClassManual").value == 'Planning' || dojo.byId("objectClassManual").value == 'PlanningWorkPlan' || dojo.byId("objectClassManual").value == 'VersionsPlanning' || dojo.byId("objectClassManual").value == 'ResourcePlanning')) objectClass='Activity';
    else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) objectClass=dojo.byId('objectClass').value;
    if (objectClass.substr(0,7) == 'Report_') objectClass=objectClass.substr(7);
    if (dijit.byId('filterCompareParameter').get('checked')) {
      dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
        display:'block'
      });
      if (isNewGui) dojo.byId("filterCompareParameterPane").style.left="200px";
      if (isNewGui) dojo.byId("filterCompareParameterPane").style.top=(dojo.byId("filterDynamicParameterPane").style.display=='none')?"9px":"30px";
      dojo.style(dijit.byId('filterValue').domNode,{
        display:'none'
      });
      dojo.style(dijit.byId('filterValueList').domNode,{
        display:'none'
      });
      dojo.style(dijit.byId('filterValueCheckbox').domNode,{
        display:'none'
      });
      dojo.style(dijit.byId('filterValueDate').domNode,{
        display:'none'
      });
      dojo.style(dijit.byId('filterSortValueList').domNode,{
        display:'none'
      });
      refreshListSpecific('object','FilterCompareAttribute','objectClass',objectClass, null, false, 'field', value);
    }else{
      dojo.style(dijit.byId('FilterCompareAttribute').domNode,{
        display:'none'
      }); 
    }    //#6012
  }
  if (editFilterValueCompare && dijit.byId('filterCompareParameterSwitch')){
    dijit.byId('filterCompareParameterSwitch').set('value','on');
    dojo.byId('filterDynamicParameterPane').style.display = 'none';
  }else{
    dijit.byId('filterCompareParameterSwitch').set('value','off');
  }
  if (editFilterValueDynamic !== null && editFilterValueDynamic == 1 && dijit.byId('filterCompareParameterSwitch')){
    dijit.byId('filterDynamicParameterSwitch').set('value','on');
  }else{
    dijit.byId('filterDynamicParameterSwitch').set('value','off');
  }
  if (editFilterOperator!='NOT IN' && editFilterOperator!='IN' ){
    dojo.byId('showDetailInFilter').style.display='none'; 
  }else{
    dojo.byId('showDetailInFilter').style.display='block';
  }
}

function addfilterClause(silent,updateFilter,replaceIdFilterClause) {
  filterStartInput=false;
  var isGroup = dojo.byId('isGroup');
  var indentLevel = dojo.byId('indentLevel');
  
  if (dijit.byId('filterNameDisplay')) {
    dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
  }
  if (filterType == "") {
    if (!silent) showAlert(i18n('attributeNotSelected'));
    return;
  }
  if (trim(dijit.byId('idFilterOperator').get('value')) == '') {
    if (!silent) showAlert(i18n('operatorNotSelected'));
    return;
  }
  if (!dijit.byId('filterDynamicParameter').get('checked')) {
    if (!dijit.byId('filterCompareParameter').get('checked')) {
      if (filterType == "list" && trim(dijit.byId('filterValueList').get('value')) == '') {
        if (!silent) showAlert(i18n('valueNotSelected'));
        return;
      }
    }
    if (!dijit.byId('filterCompareParameter').get('checked')) {
      if (filterType == "date" && !dijit.byId('filterValueDate').get('value')) {
        if (!silent) showAlert(i18n('valueNotSelected'));
        return;
      }
    }
    if (!dijit.byId('filterCompareParameter').get('checked')) {
      if (filterType == "text" && !dijit.byId('filterValue').get('value')) {
        if (!silent) showAlert(i18n('valueNotSelected'));
        return;
      }
    }else{
      if (filterType == "text" && !dijit.byId('FilterCompareAttribute').get('value')) {
        if (!silent) showAlert(i18n('valueNotSelected'));
        return;
      }
    }
    if (dijit.byId('idFilterAttribute').get('value') == 'idle' && dijit.byId('idFilterOperator').get('value') == '=' && dijit.byId('filterValueCheckbox').get('checked')) {
      dijit.byId('listShowIdle').set('checked',true);
    }
  }
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
  compUrl += (compUrl ? "&" : "?") + "updateFilter=" + (updateFilter ? "true" : "false");
  compUrl += "&replaceFilterClause=" +(replaceIdFilterClause != undefined ? replaceIdFilterClause : '');
  dojo.byId('filterEditId').value=(updateFilter)?editFilterIndex:null;
  loadContent("../tool/addFilterClause.php" + compUrl,"listFilterClauses","dialogFilterForm",false,null,null,null,function() {
    clearDivDelayed('saveFilterResult');
    isGroup.value = "0";
  });
  if (dojo.byId('filterLogicalOperator') && dojo.byId('filterLogicalOperator').style.display == 'none') {
    dojo.byId('filterLogicalOperator').style.display='block';
  }
  dojo.byId('buttonSaveFieldEdition').style.display='none';
}

function removefilterClause(id) {
  if (dijit.byId('filterNameDisplay')) {
    dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
  }
  if (dijit.byId('idLayout')) {
    dojo.byId('filterLayout').value=dijit.byId('idLayout').get('value');
  }
  dojo.byId("filterClauseId").value=id;
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
  loadContent("../tool/removeFilterClause.php" + compUrl ,"listFilterClauses","dialogFilterForm",false);
  if (id == 'all' || dojo.byId('nbFilterCriteria').value == "1") { // Value is
    // not set
    // to 0
    // already
    // but is
    // going to
    dojo.byId('filterLogicalOperator').style.display='none';
  } else if (dojo.byId('nbFilterCriteria').value == "2") { // Value is going to
    // be set at 1
    loadContent("../tool/displayFilterClause.php" + compUrl,"listFilterClauses","dialogFilterForm",false,null,null,null,function() {
      clearDivDelayed('saveFilterResult');
    });
  }
}

function selectFilter(skipRefresh) {
  if (skipRefresh==null || skipRefresh==undefined) skipRefresh=false;
  if (!validateFilterParentheses()) {
    return false;
  }
  if (filterStartInput) {
    addfilterClause(true);
    setTimeout("selectFilterContinue("+((skipRefresh)?'true':'false')+");",1000);
  } else {
    selectFilterContinue(skipRefresh);
  }
}

function selectFilterContinue(skipRefresh) {
  if (skipRefresh==null || skipRefresh==undefined) skipRefresh=false;
  if (window.top.dijit.byId('dialogDetail').open) {
    var doc=window.top.frames['comboDetailFrame'];
  } else {
    var doc=window.top;
  }
  if (dijit.byId('filterNameDisplay')) {
    dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
  }
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
  dojo.xhrPost({
    url:"../tool/backupFilter.php?valid=true" + compUrl +addTokenIndexToUrl(),
    form:'dialogFilterForm',
    handleAs:"text",
    load:function(data,args) {
    }
  });
  if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) {
    objectClass=dojo.byId('objectClassList').value;
  } else if (!window.top.dijit.byId('dialogDetail').open && dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value) {
    objectClass=dojo.byId("objectClassManual").value;
  } else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) {
    objectClass=dojo.byId('objectClass').value;
  }
  if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value == 'Kanban') {
    compUrl+='&context=directFilterList';
    compUrl+='&contentLoad=../view/kanbanView.php';
    compUrl+='&container=divKanbanContainer';
  }
  doc.loadContent("../tool/displayFilterList.php?displayQuickFilter=true&context=directFilterList&filterObjectClass=" + objectClass + compUrl,
                  "directFilterList",null,false,'returnFromFilter'+((skipRefresh)?'SkipRefresh':''),false);
  /*
   * florent Ticket #4010 When adding filter (not stored), icon has not the "on"
   * flag
   */
  if (dojo.byId("nbFilterCriteria").value > 0 && !dijit.byId('filterDynamicParameter').get("checked") && dojo.byId('nbDynamicFilterCriteria').value == 0) {
    setTimeout("dijit.byId('listFilterFilter').set('iconClass', 'dijitButtonIcon iconActiveFilter')",500);
  } else {
    setTimeout("dijit.byId('listFilterFilter').set('iconClass', 'dijitButtonIcon iconFilter')",500);
  }
  if (skipRefresh) {
	dijit.byId("dialogFilter").hide();
	filterStartInput=false;
	return;
  }
  if (!window.top.dijit.byId('dialogDetail').open && dojo.byId('objectClassManual') && (dojo.byId('objectClassManual').value == 'Kanban' || dojo.byId('objectClassManual').value == 'LiveMeeting')) {
    loadContent("../view/kanbanView.php?idKanban=" + dojo.byId('idKanban').value,"divKanbanContainer");
  } else if (!dijit.byId('filterDynamicParameter').get("checked")) {
    if (dojo.byId("objectClassManual") && (dojo.byId("objectClassManual").value == 'Planning' || dojo.byId("objectClassManual").value == 'PlanningWorkPlan') && !window.top.dijit.byId('dialogDetail').open) {
      refreshJsonPlanning();
    } else if (dojo.byId("objectClassManual") && (dojo.byId("objectClassManual").value == 'VersionsPlanning' || dojo.byId("objectClassManual").value == 'ResourcePlanning')
        && !window.top.dijit.byId('dialogDetail').open) {
      if (dojo.byId("objectClassManual").value == 'VersionsPlanning') {
        refreshJsonPlanning('version');
      } else {
        refreshJsonPlanning('resource');
      }
    } else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value == 'Report') {
      dojo.byId('outMode').value='';
      runReport();
    } else {
      doc.refreshJsonList(objectClass);
    }
  }
  dijit.byId("dialogFilter").hide();
  filterStartInput=false;
}

function cancelFilter() {
  filterStartInput=true;
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
  dojo.xhrPost({
    url:"../tool/backupFilter.php?cancel=true" + compUrl +addTokenIndexToUrl(),
    form:'dialogFilterForm',
    handleAs:"text",
    load:function(data,args) {
    }
  });
  dijit.byId('dialogFilter').hide();
}

function clearFilter() {
  if (dijit.byId('filterNameDisplay')) {
    dijit.byId('filterNameDisplay').reset();
  }
  if (dijit.byId('idLayout')) {
    dijit.byId('idLayout').reset();
  }
  dojo.byId('filterName').value="";
  dojo.byId('filterLayout').value="";
  removefilterClause('all');
  // setTimeout("selectFilter();dijit.byId('listFilterFilter').set('iconClass','dijitButtonIcon
  // iconFilter');",100);
  dijit.byId('listFilterFilter').set('iconClass','dijitButtonIcon iconFilter');
  dijit.byId('filterNameDisplay').set('value',null);
  dojo.byId('filterName').value=null;
  dijit.byId('idLayout').set('value',null);
  dojo.byId('filterLayout').value=null;
  if (dojo.byId("operatorFilterAndOrJs") && dojo.byId("parenthesisOpenJs")){
    dojo.byId("operatorFilterAndOrJs").remove();
    dojo.byId("parenthesisOpenJs").remove();
  }
  resetOpenCloseGroup();
}

function defaultFilter(removeDefaultFilter,filterName) {
  if (!removeDefaultFilter) removeDefaultFilter=false; 
  if (!filterName) filterName=null;
  if (dijit.byId('filterNameDisplay') && !removeDefaultFilter && filterName==null) {
    dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
  }else if (filterName){
    dojo.byId('filterName').value=filterName;
  }
  var removeParam = (compUrl ? "&" : "?") + "removeDefaultFilter=" + (removeDefaultFilter ? "1" : "0");
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
  loadContent("../tool/defaultFilter.php" + compUrl + removeParam,"listStoredFilters","dialogFilterForm",false,null,null,null,function() {
    clearDivDelayed('saveFilterResult');
  });
}

function copyFilterShared(idFilter){
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
  var idFilter = (compUrl ? '&' : '?') + 'idFilter=' + idFilter;
  loadContent("../tool/saveFilterShared.php" + compUrl + idFilter ,"listStoredFilters",null,false,null,null,null,function() {
    clearDivDelayed('saveFilterResult');
  });
}

function saveFilter() {
  if (dijit.byId('filterNameDisplay')) {
    if (dijit.byId('filterNameDisplay').get('value') == "") {
      showAlert(i18n("messageMandatory",new Array(i18n("filterName"))));
      return;
    }
    dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
  }
  if (!validateFilterParentheses()) {
    return false;
  }
  if (dojo.byId("operatorFilterAndOrJs") && dojo.byId("parenthesisOpenJs")){
    dojo.byId("operatorFilterAndOrJs").remove();
    dojo.byId("parenthesisOpenJs").remove();
  }
  resetOpenCloseGroup();
  
  var nbFilter=dojo.byId('nbFilterCriteria');
  if (nbFilter && nbFilter.value == 0) {
    showAlert(i18n("cantSaveFilterWithNoClause"));
    return;
  }
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
  loadContent("../tool/saveFilter.php" + compUrl,"listStoredFilters","dialogFilterForm",false,null,null,null,function() {
    clearDivDelayed('saveFilterResult');
  });
}

editFilterOperator=null;
editFilterOperatorDisp=null;
editFilterValue=null;
editFilterValueDisp=null;
editFilterValueDynamic=null;
editFilterValueCompare=null;
editFilterIndex=null;
function editFilterClause(id){
	if (!dojo.byId('objectClass').value && dojo.byId('objectClassManual').value) objectFilterClass=dojo.byId('objectClassManual').value;
	else objectFilterClass = dojo.byId('objectClass').value;
  dojo.xhrPost({
    url:"../tool/getFilterClause.php?objectClass="+objectFilterClass+"&filterClause=" + id + addTokenIndexToUrl(),
    handleAs:"text",
    load:function(data,args) {
      var res=JSON.parse(data);
      currentAttribute=dijit.byId('idFilterAttribute').value;
      editFilterOperator=res.sql.operator;
      editFilterOperatorDisp=res.disp.operator;
      editFilterValue=res.sql.value;
      editFilterValueDisp=res.disp.value;
      editFilterIndex=id;
      if (id=='0' && dojo.byId('filterLogicalOperator')) dojo.byId('filterLogicalOperator').style.display='none';
      editFilterValueCompare=(editFilterValue && editFilterValue[0]=='[')?true:false; 
      editFilterValueDynamic=res.isDynamic;
      dijit.byId('orOperator').set('value',res.orOperator);
      dijit.byId('idFilterAttribute').set('value',res.sql.attribute);
      if (currentAttribute==res.sql.attribute) filterSelectAtribute(currentAttribute);
      setTimeout("editFilterOperator=null;editFilterValue=null;editFilterValueDynamic=null;editFilterValueCompare=null;editFilterOperatorDisp=null;editFilterValue=null;",2000);
      
    }
  });
}

function openCloseGroup() {
  var button = dojo.byId("dialogFilterOpenClose");
  var triangle = dojo.byId("cursorFilter");
  var isGroup = dojo.byId("isGroup");
  var indentLevel = dojo.byId("indentLevel");
  
  if (button.getAttribute("data-state") == "open") {
    if (dojo.byId("operatorFilterAndOrJs") || dojo.byId("parenthesisOpenJs")) return;
    dojo.byId("dialogFilterOpenClose_label").innerHTML = i18n('closeGroup');
    button.setAttribute("data-state", "closed");
    button.setAttribute("title", i18n('closeGroupFilter'));
    isGroup.value = "1";
    indentLevel.value = "1";
    var spanContainer = null;
    if (triangle){
      spanContainer = triangle.parentNode;
    }
    else {
      var cursorNoFilterClause = dojo.byId('cursorFilterNoFilterClause');
      cursorNoFilterClause.style.display='block';
      spanContainer = cursorNoFilterClause.parentNode;
    }

    if (triangle) {
      var operatorDiv = document.createElement("div");
      operatorDiv.className = "operatorFilterAndOr";
      const opVal = dijit.byId("orOperator").get("value");
      operatorDiv.style.backgroundColor = (opVal === "1") ? "#e97c2d" : "#10c621";
      operatorDiv.innerHTML = i18n(opVal === "1" ? 'OR' : 'AND');
      operatorDiv.id = "operatorFilterAndOrJs";
    }
    const parenthesisDiv = document.createElement("div");
    parenthesisDiv.className = "parenthesisFilter";
    parenthesisDiv.id = "parenthesisOpenJs";
    parenthesisDiv.innerHTML = "(";
    if (spanContainer && spanContainer.parentNode) {
      if (triangle) {
        spanContainer.parentNode.insertBefore(operatorDiv, spanContainer.nextSibling);
        spanContainer.parentNode.insertBefore(parenthesisDiv, operatorDiv.nextSibling);
      } else {
        spanContainer.parentNode.insertBefore(parenthesisDiv, spanContainer.nextSibling);
      }
    }
  } else {
    if (dojo.byId("operatorFilterAndOrJs") && dojo.byId("parenthesisOpenJs")){
      dojo.byId("operatorFilterAndOrJs").remove();
      dojo.byId("parenthesisOpenJs").remove();
    }
    dojo.byId("dialogFilterOpenClose_label").innerHTML = i18n('openGroup');
    button.setAttribute("data-state", "open");
    button.setAttribute("title", i18n('openGroupFilter'));
    if (triangle) {
      triangle.style.marginLeft = "0px";
    }
    isGroup.value = "0";
    indentLevel.value = "0";
    const filterDataTds = document.querySelectorAll('td[id^="filterData_"]');
    var maxIndex = -1;
    var lastTd = null;
    filterDataTds.forEach(td => {
      const match = td.id.match(/^filterData_(\d+)$/);
      if (match) {
        const num = parseInt(match[1], 10);
        if (num > maxIndex) {
          maxIndex = num;
          lastTd = td;
        }
      }
    });
    if (document.querySelector('[id^="parenthesisOpen_"]') && lastTd && maxIndex >= 0 && !document.getElementById("parenthesisCloseJs_" + maxIndex) && !document.getElementById("parenthesisClose_" + maxIndex)) {
      var idReplace = maxIndex-1;
      if (document.getElementById("parenthesisOpen_" + maxIndex)){
        addfilterClause(null,false,idReplace);
      }else {
        const closeDiv = document.createElement("div");
        closeDiv.className = "parenthesisFilter";
        closeDiv.id = "parenthesisCloseJs_" + maxIndex;
        closeDiv.innerHTML = ")";
        lastTd.appendChild(closeDiv);
        addfilterClause(null,false,idReplace); 
      } 
    }
  }
}

function resetOpenCloseGroup(){
  var button = dojo.byId("dialogFilterOpenClose");
  if (button){
    dojo.byId("dialogFilterOpenClose_label").innerHTML = i18n('openGroup');
    button.setAttribute("data-state", "open");
    button.setAttribute("title", i18n('openGroupFilter'));
    dojo.byId("isGroup").value = "0";
    dojo.byId("indentLevel").value = "0";
  }
}

function validateFilterParentheses() {
  const opens = document.querySelectorAll("[id^='parenthesisOpen_']").length;
  const closes = document.querySelectorAll("[id^='parenthesisClose_']").length;
  if (opens>closes){
    showAlert(i18n("groupNotClose"));
    return;
  }
  if (opens<closes){
    showAlert(i18n("groupNotOpen"));
    return;
  }
  return true;
}



/**
 * Select a stored filter in the list and fetch criteria
 * 
 */
var globalSelectFilterContentLoad=null;
var globalSelectFilterContainer=null;
function selectStoredFilter(idFilter,idLayout,context,contentLoad,container) {
  if (dojo.byId('buttonSaveFieldEdition')) dojo.byId('buttonSaveFieldEdition').style.display='none';
  resetOpenCloseGroup();
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
  globalSelectFilterContentLoad=null;
  globalSelectFilterContainer=null;
  var callBack= function(){
    validateLayoutListColumn();
  };
  if (context == 'directFilterList') {
    if (dojo.byId('noFilterSelected')) {
      if (idFilter == '0') {
        dojo.byId('noFilterSelected').value='true';
      } else {
        dojo.byId('noFilterSelected').value='false';
      }
    } else if (window.top.dojo.byId('noFilterSelected')) {
      if (idFilter == '0') {
        window.top.dojo.byId('noFilterSelected').value='true';
      } else {
        window.top.dojo.byId('noFilterSelected').value='false';
      }
    }
    if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) objectClass=dojo.byId('objectClassList').value;
    else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value) objectClass=dojo.byId("objectClassManual").value;
    else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) objectClass=dojo.byId('objectClass').value;
    if (objectClass=="PlanningWorkPlan") objectClass="Planning";
    var validationType=(idFilter==0 || ! idLayout)?null:'skipRefresh';
    var currentScreen = (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value)?dojo.byId("objectClassManual").value:null;
    if (dojo.byId('dynamicFilterId' + idFilter)) {
      var param="&idFilter=" + idFilter + "&filterObjectClass=" + objectClass;
      loadDialog('dialogDynamicFilter',null,true,param,true);
      globalSelectFilterContentLoad=contentLoad;
      globalSelectFilterContainer=container;
      validationType='selectFilter'; // will avoid immediate refresh
    }
    if (typeof contentLoad != 'undefined' && typeof container != 'undefined') {
      if(idLayout !=0){
        loadContent("../tool/selectStoredFilter.php?currentscreen="+currentScreen+"&idFilter=" + idFilter + "&context=" + context + "&contentLoad=" + contentLoad + "&container=" + container + "&filterObjectClass=" + objectClass
            + compUrl,"directFilterList",null,false,validationType, null, null, callBack); 
      }else{
        loadContent("../tool/selectStoredFilter.php?currentscreen="+currentScreen+"&idFilter=" + idFilter + "&context=" + context + "&contentLoad=" + contentLoad + "&container=" + container + "&filterObjectClass=" + objectClass
            + compUrl,"directFilterList",null,false,validationType); 
      }
      if (!dojo.byId('dynamicFilterId' + idFilter)) loadContent(contentLoad,container);
    } else {
      if(idLayout !=0){
        loadContent("../tool/selectStoredFilter.php?currentscreen="+currentScreen+"&idFilter=" + idFilter + "&context=" + context + "&filterObjectClass=" + objectClass + compUrl,"directFilterList",null,false,validationType, null, null, callBack);
      } else {
        loadContent("../tool/selectStoredFilter.php?currentscreen="+currentScreen+"&idFilter=" + idFilter + "&context=" + context + "&filterObjectClass=" + objectClass + compUrl,"directFilterList",null,false,validationType);
      }
      if (dojo.byId("objectClassList") && dojo.byId("objectClassList").value.substr(0,7) == 'Report_') {
        dojo.byId('outMode').value='';
        runReport();
      }
    }
    if (isNewGui) {
      dijit.byId('listFilterFilter').closeDropDown();
    }
  } else {
    var filters = document.querySelectorAll("tr[id^='filter']");
    for (var m = 0; m < filters.length; m++){
      var filterID = filters[m].id.charAt(0).toUpperCase() + filters[m].id.slice(1);
      if (filters[m].id == 'filter' + idFilter) {
        dojo.byId('filter' + idFilter).style.cssText = "color: var(--color-darker); background-color: var(--color-medium-secondary);";
        if (dojo.byId('img'+filterID)) dojo.byId('img'+filterID).style.cssText = "filter:brightness(0) invert(var(--color-toolbar-invert));float:left;width:10px;";
      } else {
        dojo.byId(filters[m].id).style.cssText = "cursor: pointer;";
        if (dojo.byId('img'+filterID)) dojo.byId('img'+filterID).style.cssText = "float:left;width:10px";
      }
    }
    if (dojo.byId('filterLogicalOperator') && dojo.byId('filterLogicalOperator').style.display == 'none') {
      dojo.byId('filterLogicalOperator').style.display='block';
    }   
    loadContent("../tool/selectStoredFilter.php?idFilter=" + idFilter + compUrl,"listFilterClauses","dialogFilterForm",false, null, null, null);
    loadContent("../tool/refreshFilterNameLayout.php?idFilter=" + idFilter + '&idLayout=' + idLayout + compUrl,"filterNameLayoutDiv","dialogFilterForm",false, null, null, null);
  }
}

function removeStoredFilter(idFilter,nameFilter) {
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
  var action=function() {
    var callBack=function() {
      clearDivDelayed('saveFilterResult');
    };
    loadContent("../tool/removeFilter.php?idFilter=" + idFilter + compUrl,"listStoredFilters","dialogFilterForm",false,null,null,null,callBack);
  };
  window.top.showConfirm(i18n("confirmRemoveFilter",new Array(nameFilter)),action);
}

function shareStoredFilter(idFilter,nameFilter) {
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
  loadContent("../tool/shareFilter.php?idFilter=" + idFilter + compUrl,"listStoredFilters","dialogFilterForm",false);
}

function selectDynamicFilter() {
  for (var i=0;i < dojo.byId('nbDynamicFilterClauses').value;i++) {
    if (dijit.byId('filterValueList' + i)) {
      if (dijit.byId('filterValueList' + i).get("value") == "") {
        showAlert(i18n('valueNotSelected'));
        return;
      }
    } else if (dijit.byId('filterValue' + i)) {
      if (dijit.byId('filterValue' + i).get("value") == "") {
        showAlert(i18n('valueNotSelected'));
        return;
      }
    } else if (dijit.byId('filterValueDate' + i)) {
      if (dijit.byId('filterValueDate' + i).get("value") == "") {
        showAlert(i18n('valueNotSelected'));
        return;
      }
    }
  }

  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
  var callBack=function() {
    selectDynamicFilterContinue();
  }
  loadContent("../tool/addDynamicFilterClause.php" + compUrl,"listDynamicFilterClauses","dialogDynamicFilterForm",false,null,null,null,callBack);
}

function selectDynamicFilterContinue() {
  if (window.top.dijit.byId('dialogDetail').open) {
    var doc=window.top.frames['comboDetailFrame'];
  } else {
    var doc=top;
  }
  if (dijit.byId('filterNameDisplay')) {
    dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
  }
  doc.dijit.byId("listFilterFilter").set("iconClass","dijitButtonIcon iconActiveFilter");
  if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) objectClass=dojo.byId('objectClassList').value;
  else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value) objectClass=dojo.byId("objectClassManual").value;
  else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) objectClass=dojo.byId('objectClass').value;
  var compUrl='';
  if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value == 'Kanban') {
    compUrl+='&context=directFilterList';
    compUrl+='&contentLoad=../view/kanbanView.php';
    compUrl+='&container=divKanbanContainer';
  }
  doc.loadContent("../tool/displayFilterList.php?context=directFilterList&displayQuickFilter=true&displayQuickFilter=true&filterObjectClass=" + objectClass + compUrl,"directFilterList",null,false,
      'returnFromFilter',false);

  if (dojo.byId("objectClassManual") && (dojo.byId("objectClassManual").value == 'Planning' || dojo.byId("objectClassManual").value == 'PlanningWorkPlan') && !window.top.dijit.byId('dialogDetail').open) {
    refreshJsonPlanning();
  } else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value == 'Report') {
    dojo.byId('outMode').value='';
    runReport();
  } else if (doc.dojo.byId('objectClassList')) {
    doc.refreshJsonList(doc.dojo.byId('objectClassList').value);
  } else {
    doc.refreshJsonList(doc.dojo.byId('objectClass').value);
  }
  dijit.byId("dialogDynamicFilter").hide();
}

function updateShowTagState(tag, id){
  if(dojo.hasClass(tag, 'docLineTag')){
    dojo.removeClass(tag, 'docLineTag');
    dojo.addClass(tag, 'docLineTagNew');
    dijit.byId('showTags'+id).set('checked', true);
  }else if(dojo.hasClass(tag, 'docLineTagNew')){
    dojo.removeClass(tag, 'docLineTagNew');
    dojo.addClass(tag, 'docLineTag');
    dijit.byId('showTags'+id).set('checked', false);
  }
}

function updateShowStatusState(Status, id, color){
  if(dojo.hasClass(Status, 'docLineTag')){
    dojo.removeClass(Status, 'docLineTag');
    dojo.addClass(Status, 'docLineTagNew');
    Status.style.background = color;
    Status.style.color = getForeColor(color);
    dijit.byId('showStatus'+id).set('checked', true);
  }else if(dojo.hasClass(Status, 'docLineTagNew')){
    dojo.removeClass(Status, 'docLineTagNew');
    Status.style.background = null;
    Status.style.color = null;
    dojo.addClass(Status, 'docLineTag');
    dijit.byId('showStatus'+id).set('checked', false);
  }
}

/*
 * Ticket #3988 - Object list : boutton reset parameters florent
 */
function resetFilter(lstStat, lstTags) {
  var grid=dijit.byId("objectGrid");
  var notDef;
  var i=0;
  for (var i=1;i <= lstStat;i++) {
    if (dijit.byId('showStatus' + i)) {
      dijit.byId('showStatus' + i).set('checked',false);
    }
  }
  dojo.query('#barFilterByStatus .docLineTagNew').forEach(function(node,index,nodelist) {
    dojo.removeClass(node, 'docLineTagNew');
    node.style.background = null;
    node.style.color = null;
    dojo.addClass(node, 'docLineTag');
  });
  i=0;
  for (var i=1;i <= lstTags;i++) {
    if (dijit.byId('showTags' + i)) {
      dijit.byId('showTags' + i).set('checked',false);
    }
  }
  dojo.query('#barFilterByTags .docLineTagNew').forEach(function(node,index,nodelist) {
    dojo.removeClass(node, 'docLineTagNew');
    dojo.addClass(node, 'docLineTag');
  });

  if (dijit.byId("listFilterFilter").iconClass == "dijitButtonIcon iconActiveFilter") {
    selectStoredFilter('0','0','directFilterList',notDef,notDef);
  }
  if (grid) {
    if (dijit.byId('listTypeFilter')) {
      dijit.byId('listTypeFilter').set('value','');
    }
    if (dijit.byId('listClientFilter')) {
      dijit.byId('listClientFilter').set('value','');
    }
    if (dijit.byId('listItemSelector')) {
      dijit.byId('listItemSelector').set('value','');
    }
    if (dijit.byId('showAllProjects')) {
      dijit.byId('showAllProjects').set('value','');
    }
    if (dijit.byId('ListPredefinedActions')) {
      dijit.byId('ListPredefinedActions').set('value','');
    }
    if (dijit.byId('ListBudgetParentFilter')) {
      dijit.byId('ListBudgetParentFilter').set('value','');
    }
    if (dijit.byId('ListBudgetParentFilter')) {
      dijit.byId('ListBudgetParentFilter').set('value','');
    }
    if (dijit.byId('ListShowIdle')) {
      dijit.byId('ListShowIdle').set('value','');
    }
    if (dijit.byId('hideInService')) {
      dijit.byId('hideInService').set('value','');
    }
    if (dojo.byId("listQuickSearchValueFilter")){
      dijit.byId('listQuickSearchFilter').set('value','');
    }
    if (dijit.byId('listIdFilter') || dijit.byId('listNameFilter') || dijit.byId('listNameFilter') && dijit.byId('listIdFilter')) {
      dijit.byId('listIdFilter').set('value','');
      dijit.byId('listNameFilter').set('value','');
      filter={};
      grid.query=filter;
      grid._refresh();
    }
  }

}

function resetFilterQuick(lstStat, lstTags) {
  var grid=dijit.byId("objectGrid");
  var notDef;
  var i=0;
  for (var i=1;i <= lstStat;i++) {
    if (dijit.byId('showStatus' + i)) {
      dijit.byId('showStatus' + i).set('checked',false);
    }
  }
  if (lstStat != null ){
    dojo.query('#barFilterByStatus .docLineTagNew').forEach(function(node,index,nodelist) {
      dojo.removeClass(node, 'docLineTagNew');
      node.style.background = null;
      node.style.color = null;
      dojo.addClass(node, 'docLineTag');
    });
  } 
  i=0;
  for (var i=1;i <= lstTags;i++) {
    if (dijit.byId('showTags' + i)) {
      dijit.byId('showTags' + i).set('checked',false);
    }
  }
  if (lstTags != null ){
    dojo.query('#barFilterByTags .docLineTagNew').forEach(function(node,index,nodelist) {
      dojo.removeClass(node, 'docLineTagNew');
      dojo.addClass(node, 'docLineTag');
    }); 
  }

  if (dijit.byId("listFilterFilter").iconClass == "dijitButtonIcon iconActiveFilter") {
    selectStoredFilter('0','0','directFilterList',notDef,notDef);
  }
  if (grid) {
    if (dijit.byId('listTypeFilter')) {
      dijit.byId('listTypeFilter').set('value','');
    }
    if (dijit.byId('listClientFilter')) {
      dijit.byId('listClientFilter').set('value','');
    }
    if (dijit.byId('listItemSelector')) {
      dijit.byId('listItemSelector').set('value','');
    }
    if (dijit.byId('showAllProjects')) {
      dijit.byId('showAllProjects').set('value','');
    }
    if (dijit.byId('ListPredefinedActions')) {
      dijit.byId('ListPredefinedActions').set('value','');
    }
    if (dijit.byId('ListBudgetParentFilter')) {
      dijit.byId('ListBudgetParentFilter').set('value','');
    }
    if (dijit.byId('ListShowIdle')) {
      dijit.byId('ListShowIdle').set('value','');
    }
    if (dijit.byId('hideInService')) {
      dijit.byId('hideInService').set('value','');
    }
    if (dijit.byId('listIdFilter')) {
      dijit.byId('listIdFilter').set('value','');
    }
    if (dijit.byId('listNameFilter')) {
      dijit.byId('listNameFilter').set('value','');
    }
    if (dojo.byId("listQuickSearchValueFilter")){
      dijit.byId('listQuickSearchFilter').set('value','');
    }
    if (dijit.byId('listIdFilter') || dijit.byId('listNameFilter')) {
      filter={};
      grid.query=filter;
      grid._refresh();
    }
  }

  if (dijit.byId('listIdFilterQuick')) {
    dijit.byId('listIdFilterQuick').set('value','');
    if (dijit.byId('listIdFilterQuickSw').get('value') == 'off') {
      dojo.byId('filterDivsSpan').style.display="none";
      dijit.byId('listIdFilter').domNode.style.display='none';
    }
  }
  if (dijit.byId('listNameFilterQuick')) {
    dijit.byId('listNameFilterQuick').set('value','');
    if (dijit.byId('listNameFilterQuickSw').get('value') == 'off') {
      dojo.byId('listNameFilterSpan').style.display="none";
      dijit.byId('listNameFilter').domNode.style.display='none';
    }
  }
  if (dijit.byId('listTypeFilterQuick')) {
    dijit.byId('listTypeFilterQuick').set('value','');    
    if (dijit.byId('listTypeFilterQuickSw').get('value') == 'off') {
      dojo.byId('listTypeFilterSpan').style.display="none";
      dijit.byId('listTypeFilter').domNode.style.display='none';
    }
  }
  if (dijit.byId('listClientFilterQuick')) {
    dijit.byId('listClientFilterQuick').set('value','');
    if (dijit.byId('listClientFilterQuickSw').get('value') == 'off') {
      dojo.byId('listClientFilterSpan').style.display="none";
      dijit.byId('listClientFilter').domNode.style.display='none';
    }
  }
  if (dijit.byId('listBudgetParentFilterQuick')) {
    dijit.byId('listBudgetParentFilterQuick').set('value','');
    if (dijit.byId('listBudgetParentFilterQuickSw').get('value') == 'off') {
      dojo.byId('listBudgetParentFilterSpan').style.display="none";
      dijit.byId('listBudgetParentFilter').domNode.style.display='none';
    }
  }

  if (dijit.byId('quickSearchValueQuick')) {
    dijit.byId('quickSearchValueQuick').set('value','');
    quickSearchClose();
    if (dijit.byId('quickSearchValueQuickSw').get('value') == 'off') {
      dojo.byId('listQuickSearchFilterSpan').style.display="none";
      dijit.byId('listQuickSearchFilter').domNode.style.display = "none";
      dojo.byId('listQuickSearchFilterBtnSearch').style.display = "none";
      dojo.byId('listQuickSearchFilterBtnClose').style.display = "none";
    }
  }
}

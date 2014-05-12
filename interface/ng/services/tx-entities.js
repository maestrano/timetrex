"use strict";

// This service is used to interact with Timetrex's backend
// It defines entities that can be retrieved and saved
var module = angular.module('tx.entities',[]);

// User entity
module.factory('UserEntity', [
'$http', '$cookies', '$q', '$window',
function($http, $cookies, $q, $window) {
  var service = {};
  service.data = {};
  $.cookie.defaults.path = '/';
  
  // Load a user which is then accessible via
  // UserEntity.user
  // Return a promise
  service.load = function() {
    var qLoad = $q.defer();
    
    $http.get("/api/json/api.php",
    { 
      params: {
        Class: "APIAuthentication",
        Method: "getCurrentUser",
        SessionID: $.cookie('SessionID')
      }
    }
    ).then(function(response){
      _.extend(service.data,response.data);
      qLoad.resolve(service.data);
    });
    
    return qLoad.promise;
  };
  
  // Log the user out
  service.logout = function() {
    $.removeCookie('PHPSESSID');
    $.removeCookie('SessionID');
    $.removeCookie('StationID');
    
    // The logoutRedirect variable is set on the index
    // page
    if ($window.logoutRedirect != undefined){
      $window.location.href = $window.logoutRedirect;
    } else {
      $window.location.href = "/";
    };
    
  };
  
  return service;
}]);

// Timesheet entity
module.factory('TimesheetEntity', [
'$http', '$cookies', '$q', 'PunchEntity', 'PaystubEntity',
function($http, $cookies, $q, PunchEntity, PaystubEntity) {
  var service = {};
  service.meta = {};
  service.data = {};
  service.payStubsdata = {};
  service.branches = {};
  service.departments = {};
  service.currentDetails = {};
  service.temporaryAddedRows = [];
  service.travelPayStubAccountId = 31; // Travel Expenses
  service.zoneConfig = [
    {name: 'Zone 1', rate: 15.0000, desc: '1-8kms'},
    {name: 'Zone 2', rate: 20.0000, desc: '9-14kms'},
    {name: 'Zone 3', rate: 25.0000, desc: '15-19kms'},
    {name: 'Zone 4', rate: 30.0000, desc: '20-24kms'},
    {name: 'Zone 5', rate: 40.0000, desc: '25-30kms'},
    {name: 'Zone 6', rate: 45.0000, desc: '31-36kms'}
  ];
  
  // Load a user which is then accessible via
  // UserEntity.user
  // Return a promise
  service.load = function(userId,baseDate) {
    if (userId != undefined) {
      service.currentDetails.userId = userId;
    } else {
      userId = service.currentDetails.userId;
    };
    
    var timesheetHardReset = false;
    if (baseDate != undefined) {
      if(service.currentDetails.baseDate == undefined || service.currentDetails.baseDate.getTime() != baseDate.getTime()) {
        service.currentDetails.baseDate = new Date(baseDate);
        timesheetHardReset = true;
      };
    } else {
      baseDate = new Date(service.currentDetails.baseDate);
    };
    
    // Reset the temporary values
    service.temporaryAddedRows.length = 0;
    
    // Declare loading promise
    var qLoad = $q.defer();
    var qTimesheetDataLoad = $q.defer();
    
    // Load timesheet data
    var q1 = qTimesheetDataLoad.promise;
    $http.get("/api/json/api.php",
    {
      params: {
        Class: "APITimeSheet",
        Method: "getTimeSheetData",
        SessionID: $cookies.SessionID,
        json: {0: userId, 1: baseDate }
      }
    }
    ).then(function(response){
      // Reset and populate the timesheet
      for (var key in service.data) {
        if (service.data.hasOwnProperty(key)) {
          delete service.data[key];
        };
      };
      _.extend(service.data,response.data);
      
      $http.get("/api/json/api.php",
      {
        params: {
          Class: "APIPayStubAmendment",
          Method: "getPayStubAmendment",
          SessionID: $cookies.SessionID,
          json: {0: {filter_data:{
              start_date: service.data.timesheet_dates.start_date,
              end_date: service.data.timesheet_dates.end_date
            }}
          }
        }
      }).then(function(response){
        //console.log("Paystub data");
        //console.log(response);
        for (var key in service.payStubsdata) {
          if (service.payStubsdata.hasOwnProperty(key)) {
            delete service.payStubsdata[key];
          };
        };
        
        _.extend(service.payStubsdata,response.data);
        
        
        // Build the simpleTimesheet and resolve the promise
        service.buildGlobalTimesheet(timesheetHardReset);
        qTimesheetDataLoad.resolve(service.data);
      }); 
      
      
    });
    
    // Load departments
    var q2 = $http.get("/api/json/api.php",
    {
      params: {
        Class: "APIDepartment",
        Method: "getDepartment",
        SessionID: $cookies.SessionID
      }
    }
    ).then(function(response){
      _.each(service.departments, function(value,key){
        delete service.departments[key];
      });
      
      service.departments[0] = {name:'--',manual_id:0};
      _.each(response.data, function(value,key){
        service.departments[value.manual_id] = value;
      });
    });
    
    // Load branches
    var q3 = $http.get("/api/json/api.php",
    {
      params: {
        Class: "APIBranch",
        Method: "getBranch",
        SessionID: $cookies.SessionID
      }
    }
    ).then(function(response){
      _.each(service.branches, function(value,key){
        delete service.branches[key];
      });
      
      service.branches[0] = {name:'--',manual_id:0};
      _.each(response.data, function(value,key){
        service.branches[value.manual_id] = value;
      });
    });
    
    // Resolve loading promise once all data
    // are loaded
    $q.all([q1,q2,q3]).then(function(values){
      qLoad.resolve(service.data);
    });
    
    return qLoad.promise;
  };
  
  // Return how many rows are in the timesheet
  service.rowCount = function(){
    var size = 0;
    if (service.simpleTimesheet){
      for (var key in service.simpleTimesheet) {
        if (service.simpleTimesheet.hasOwnProperty(key)) size++;
      };
    };
    
    return size;
  }
  
  // Build a new row in the simple timesheet if it does not
  // exist already
  // Return the new row key
  service.initSimpleTimesheetRow = function(branchId,departmentId) {
    service.simpleTimesheet = (service.simpleTimesheet || {});
    var st = service.simpleTimesheet;
    
    // Initialize branch>department
    var rowKey = branchId + '--' + departmentId;
    st[rowKey] = (st[rowKey] || {});
    st[rowKey].branchId = branchId;
    st[rowKey].$origBranchId = branchId;
    st[rowKey].departmentId = departmentId;
    st[rowKey].$origDepartmentId = departmentId;
    st[rowKey].displayOrder = (st[rowKey].displayOrder || service.rowCount());
    st[rowKey].days = (st[rowKey].days || {});
    
    // Initialize dates
    var dateIterator = moment(service.data.timesheet_dates.start_date,"X").toDate();
    for (var i = 0; i < 7; i++) {
      var dateKey = service.formatDateToKey(dateIterator);
      st[rowKey].days[dateKey] = (st[rowKey].days[dateKey] || {});
      
      var branchDeptDay = st[rowKey].days[dateKey];
      branchDeptDay.hours = (branchDeptDay.hours == undefined ? 0 : branchDeptDay.hours);
      branchDeptDay.$origHours = (branchDeptDay.$origHours == undefined ? 0 : branchDeptDay.$origHours);
      branchDeptDay.date = (new Date(dateIterator.toDateString()));
      
      // Move forward
      dateIterator.setDate(dateIterator.getDate() + 1);
    }
    
    return rowKey;
  }
  
  // Build SimpleTimesheet and SimpleZonesheet
  service.buildGlobalTimesheet = function(timesheetHardReset) {
    service.buildSimpleTimesheet(timesheetHardReset);
    service.buildSimpleZonesheet(timesheetHardReset);
    
    return true;
  }
  
  // Aggregate all punches by branch, department and day
  service.buildSimpleTimesheet = function(timesheetHardReset) {
    service.simpleTimesheet = (service.simpleTimesheet || {});
    var simpleTimesheet = service.simpleTimesheet;
    
    // Reset Timesheet
    if (timesheetHardReset) {
      //console.log("Performing hard reset");
      simpleTimesheet = service.simpleTimesheet = {};
    } else {
      for (var key in service.simpleTimesheet) {
        if (service.simpleTimesheet.hasOwnProperty(key)) {
          delete service.simpleTimesheet[key];
        };
      };
    }
    
    // We iterate through all punches and aggregate them in
    // branch > department > day
    // For each day we store the date in date format, the list
    // of punches related to it as well as the number of hours worked
    _.each(service.data.punch_data, function(punch) {
      // Initialize each day for this branch>department
      var rowKey = service.initSimpleTimesheetRow(punch.branch_id,punch.department_id);
      var rowObj = service.simpleTimesheet[rowKey];
      
      // Add punch to the right day and count hours
      //console.log(punch);
      var punchDate = moment(punch.actual_time_stamp,"DD-MMM-YY hh:mm a",'en').toDate();
      var punchDateKey = service.formatDateToKey(punchDate);
      var dayObj = rowObj.days[punchDateKey];
      //console.log(punchDateKey);
      // Add punch hour if "Out" and substract it if "In"
      dayObj.hours += punchDate.getHours() * (punch.status == "In" ? -1 : 1);
      
      // Save the original number of hours
      dayObj.$origHours = dayObj.hours;
      
      // Add the punch to the list
      dayObj.punches = (dayObj.punches || []);
      dayObj.punches.push(punch);
    });
    
    return simpleTimesheet;
  };
  
  // Build the zonesheet
  service.buildSimpleZonesheet = function(timesheetHardReset) {
    service.simpleZonesheet = (service.simpleZonesheet || {});
    var simpleZonesheet = service.simpleZonesheet;
    
    // Reset zonesheet
    if (timesheetHardReset) {
      //console.log("Performing hard reset");
      simpleZonesheet = service.simpleZonesheet = {};
    } else {
      for (var key in service.simpleZonesheet) {
        if (service.simpleZonesheet.hasOwnProperty(key)) {
          delete service.simpleZonesheet[key];
        };
      };
    }
    
    // Initialize zonesheet
    var dateIterator = moment(service.data.timesheet_dates.start_date,"X").toDate();
    for (var i = 0; i < 7; i++) {
      var dateKey = service.formatDateToKey(dateIterator);
      
      _.each(service.zoneConfig, function(zoneObj){
        simpleZonesheet[zoneObj.name] = (simpleZonesheet[zoneObj.name] || {})
        simpleZonesheet[zoneObj.name].name = zoneObj.name;
        simpleZonesheet[zoneObj.name].rate = zoneObj.rate;
        simpleZonesheet[zoneObj.name].desc = zoneObj.desc;
        simpleZonesheet[zoneObj.name].days = (simpleZonesheet[zoneObj.name].days || {});
        simpleZonesheet[zoneObj.name].days[dateKey] = {};
        simpleZonesheet[zoneObj.name].days[dateKey].units = 0;
        simpleZonesheet[zoneObj.name].days[dateKey].$origUnits = 0;
        simpleZonesheet[zoneObj.name].days[dateKey].paystubs = [];
        simpleZonesheet[zoneObj.name].days[dateKey].date = new Date(dateIterator);
      })
      
      // Move forward
      dateIterator.setDate(dateIterator.getDate() + 1);
    }
    
    // Populate units
    _.each(service.payStubsdata, function(payStub){
      var zoneObj = _.findWhere(service.zoneConfig, {rate: parseFloat(payStub.rate)});
      if (zoneObj != undefined && parseInt(payStub.pay_stub_entry_name_id) == service.travelPayStubAccountId) {
        var intUnits = Math.ceil(parseFloat(payStub.units));
        var zoneName = zoneObj.name;
        var dateKey = service.formatDateToKey(moment(payStub.effective_date,"DD-MMM-YY hh:mm a",'en').toDate());
        
        simpleZonesheet[zoneName].days[dateKey].units += intUnits;
        simpleZonesheet[zoneName].days[dateKey].$origUnits = simpleZonesheet[zoneName].days[dateKey].units;
        simpleZonesheet[zoneName].days[dateKey].paystubs.push(payStub);
      };
    });
    
    return simpleZonesheet;
  };
  
  // Return the total hours for the timesheet
  service.timesheetTotals = function() {
    service.timesheetTotalList = (service.timesheetTotalList || {});
    var totals = service.timesheetTotalList;
    
    // Reset totals
    for (var key in totals) {
      if (totals.hasOwnProperty(key)) {
        delete totals[key];
      };
    };
    
    // Initialize dates
    var dateIterator = moment(service.data.timesheet_dates.start_date,"X").toDate();
    for (var i = 0; i < 7; i++) {
      var dateKey = service.formatDateToKey(dateIterator);
      totals[dateKey] = 0;
      
      // Move forward
      dateIterator.setDate(dateIterator.getDate() + 1);
    }
    
    _.each(service.simpleTimesheet, function(rowObj,rowKey) {
      _.each(rowObj.days, function(dayObject,dayDateKey) {
        totals[dayDateKey] += dayObject.hours;
      });
    });
    
    return totals;
  };
  
  
  // Check that both SimpleTimesheet and SimpleZonesheet
  // are valid
  service.isGlobalTimesheetValid = function() {
    return (service.isSimpleTimesheetValid() && service.isSimpleZonesheetValid());
  };
  
  // Check wether the timesheet is valid or not
  // Validation checks that a given day does not have
  // more than 24h work registered
  service.isSimpleTimesheetValid = function() {
    var isValid = true;
    
    _.each(service.simpleTimesheet, function(branchDept,rowKey){
      _.each(branchDept.days, function(dayObj,dayKey){
        isValid = (isValid && dayObj.hours >= 0);
      });
    });
    
    _.each(service.timesheetTotals(), function(dayTotal,dayDateKey) {
      isValid = (isValid && dayTotal <= 24);
    });
    
    return isValid;
  };
  
  // Check that all units are greater than zero
  service.isSimpleZonesheetValid = function() {
    var isValid = true;
    
    _.each(service.simpleZonesheet, function(zoneObj,zoneName){
      _.each(zoneObj.days, function(dayObj,dayKey){
        isValid = (isValid && dayObj.units >= 0);
      });
    });
    
    return isValid;
  };
  
  // Check that one SimpleTimesheet or SimpleZonesheet
  // is changed
  service.isGlobalTimesheetChanged = function() {
    return (service.isSimpleTimesheetChanged() || service.isSimpleZonesheetChanged());
  };
  
  // Check that one SimpleTimesheet or SimpleZonesheet
  // is changed
  service.isGlobalTimesheetChangedForSavePurpose = function() {
    return (service.isSimpleTimesheetChangedForSavePurpose() || service.isSimpleZonesheetChanged()); 
  };
  
  // Check wether the timesheet was changed or not
  service.isSimpleTimesheetChanged = function() {
    var isChanged = false;
    
    if (service.simpleTimesheet != undefined) {
      if (service.temporaryAddedRows.length > 0){
        isChanged = true;
      };
      
      isChanged = isChanged || service.isSimpleTimesheetChangedForSavePurpose();
    };
    
    return isChanged;
  };
  
  // Check wether the timesheet was changed or not
  service.isSimpleTimesheetChangedForSavePurpose = function() {
    var isChanged = false;
    
    if (service.simpleTimesheet != undefined) {
      _.each(service.simpleTimesheet, function(rowObj,rowKey) {
        isChanged = (isChanged || rowObj.branchId != rowObj.$origBranchId);
        isChanged = (isChanged || rowObj.departmentId != rowObj.$origDepartmentId);
        
        _.each(rowObj.days, function(dayObject,dayDateKey) {
          isChanged = (isChanged || dayObject.hours != dayObject.$origHours);
        });
      });
    };
    
    return isChanged;
  };
  
  service.isSimpleZonesheetChanged = function() {
    var isChanged = false;
    
    if (service.simpleZonesheet != undefined) {
      _.each(service.simpleZonesheet, function(zoneObj,zoneName) {
        _.each(zoneObj.days, function(dayObject,dayDateKey) {
          isChanged = (isChanged || dayObject.units != dayObject.$origUnits);
        });
      });
    };
    
    return isChanged;
  };
  
  service.resetGlobalTimesheet = function() {
    service.resetSimpleTimesheet();
    service.resetSimpleZonesheet();
  };
  
  service.resetSimpleTimesheet = function() {
    if (service.simpleTimesheet != undefined) {
      _.each(service.temporaryAddedRows, function(rowKey){
        delete service.simpleTimesheet[rowKey];
      });
      
      _.each(service.simpleTimesheet, function(rowObj,rowKey) {
        rowObj.branchId = rowObj.$origBranchId;
        rowObj.departmentId = rowObj.$origDepartmentId;
        _.each(rowObj.days, function(dayObject,dayDateKey) {
          dayObject.hours = dayObject.$origHours;
        });
      });
    };
    
    return true;
  };
  
  service.resetSimpleZonesheet = function() {
    _.each(service.simpleZonesheet, function(zoneObj,zoneName) {
      _.each(zoneObj.days, function(dayObject,dayDateKey) {
        dayObject.units = dayObject.$origUnits;
      });
    });
  };
  
  service.saveGlobalTimesheet = function(){
    // Wait for the global combined promise to finish
    // then reload the timesheet
    var qFinal = $q.defer();
    $q.all([service.saveSimpleTimesheet(),service.saveSimpleZonesheet()]).then(function(values){
      service.load(service.currentDetails.userId,service.currentDetails.baseDate).then(function(value){
        qFinal.resolve(values);
      });
    });
    
    return qFinal.promise;
  };
  
  service.saveSimpleZonesheet = function(){
    var actionPromises = [];
    
    _.each(service.simpleZonesheet, function(zoneObj,rowKey) {
      _.each(zoneObj.days, function(dayObj,dayDateKey) {
        //console.log(dayObj);
        // Action is undertaken only if the number of
        // hours worked were changed for a given branch>department>day
        if (dayObj.units != dayObj.$origUnits) {
          // First create a promise for the local action
          // and add it to the array of promises
          var qLocalAction = $q.defer();
          actionPromises.push(qLocalAction.promise);
          
          // Then delete all punches for that branch>department>day
          var deletePromises = []
          _.each(dayObj.paystubs, function(paystub){
            //console.log("Deleting paystub id: " + paystub.id);
            deletePromises.push(PaystubEntity.destroy(paystub.id));
          });
          
          if (dayObj.units <= 0){
            $q.all(deletePromises).then(function(values){
              qLocalAction.resolve([{},{}]);
            });
          } else {
            // Once deleted create the new paystub
            $q.all(deletePromises).then(function(values){
              //console.log(values);
              //console.log(dayObj.date);
              
              var newPaystubData = {
                effective_date: dayObj.date.toLocaleString(),
                units: dayObj.units,
                rate: zoneObj.rate,
                user_id: service.currentDetails.userId,
                pay_stub_entry_name_id: 31
              };
            
              //console.log("Before paystub create");
              //console.log(newPaystubData)
            
              // Create paystub then resolve locaAction promise
              PaystubEntity.create(newPaystubData).then(function(value){
                //console.log("After create");
                //console.log(value);
                if(value.data && value.data.api_details && value.data.api_details.description == "INVALID DATA") {
                  service.meta.errorMsg = "It looks like something wrong happened while saving your timesheet. ";
                  service.meta.errorMsg += "Maybe your administrator did not give you the right permissions. ";
                  service.meta.errorMsg += "Please contact your application administrator. ";
                  service.meta.errorMsg += "If the problem persists please contact support@maestrano.com";
                };
                qLocalAction.resolve(value);
              });
            });
          };
        };
      });
    });
    
    // Return a global combined promise for all
    // actions
    return $q.all(actionPromises);
    
  };
  
  // Save the timesheet by going through the SimpleTimesheet
  // and deleting/creating punches when hours have changed
  service.saveSimpleTimesheet = function() {
    var actionPromises = [];
    
    _.each(service.simpleTimesheet, function(rowObj,rowKey) {
      _.each(rowObj.days, function(dayObj,dayDateKey) {
        //console.log(dayObj);
        // Action is undertaken only if the number of
        // hours worked were changed for a given branch>department>day
        if (dayObj.hours != dayObj.$origHours 
          || rowObj.branchId != rowObj.$origBranchId
          || rowObj.departmentId != rowObj.$origDepartmentId) {
          // First create a promise for the local action
          // and add it to the array of promises
          var qLocalAction = $q.defer();
          actionPromises.push(qLocalAction.promise);
          
          // Then delete all punches for that branch>department>day
          var deletePromises = []
          _.each(dayObj.punches, function(punch){
            //console.log("Deleting punch id: " + punch.id);
            deletePromises.push(PunchEntity.destroy(punch.id));
          });
          
          if (dayObj.hours <= 0){
            $q.all(deletePromises).then(function(values){
              qLocalAction.resolve([{},{}]);
            });
          } else {
            // Once deleted create one PunchIn and one PunchOut
            // for that branch>department>day to reflect the 
            // number of hours worked
            // After creation of both punches we resolve the
            // qLocalAction promise
            $q.all(deletePromises).then(function(values){
              //console.log(values);
              //console.log(dayObj.date);
              var punchInData = {
                department_id: rowObj.departmentId,
                branch_id: rowObj.branchId,
                time_stamp: dayObj.date.toLocaleString(),
                punch_time: "12:00 AM",
                status_id: 10,
                status: "In"
              };
            
              var punchOutDate = new Date(dayObj.date);
              punchOutDate.setHours(punchOutDate.getHours()+dayObj.hours);
              var punchOutData = {
                department_id: rowObj.departmentId,
                branch_id: rowObj.branchId,
                time_stamp: punchOutDate.toLocaleString(),
                punch_time: service.formatDateToTxTime(punchOutDate),
                status_id: 20,
                status: "Out"
              };
            
              //console.log("Before create");
              //console.log([punchInData,punchOutData])
            
              // Create punches then resolve locaAction promise
              // Punches NEED to be created sequentially
              PunchEntity.create(punchInData).then(function(valuePunchIn){
                PunchEntity.create(punchOutData).then(function(valuePunchOut){
                  //console.log("After create");
                  //console.log([valuePunchIn,valuePunchOut]);
                  
                  if(valuePunchIn.data && valuePunchIn.data.api_details && valuePunchIn.data.api_details.description == "INVALID DATA") {
                    service.meta.errorMsg = "It looks like something wrong happened while saving your timesheet. ";
                    service.meta.errorMsg += "Maybe your administrator did not give you the right permissions. ";
                    service.meta.errorMsg += "Please contact your application administrator. ";
                    service.meta.errorMsg += "If the problem persists please contact support@maestrano.com";
                  };
                  
                  qLocalAction.resolve([valuePunchIn,valuePunchOut]);
                });
              });
            });
          };
        };
      });
    });
    
    // Return a global combined promise for all
    // actions
    return $q.all(actionPromises);
  };
  
  
  // Return the remaining list of [branch,department]
  // combinations
  service.remainingBranchDeptCombinations = function(){
    var remainingCombinations = {};
    
    _.each(service.branches, function(branch,branchId){
      _.each(service.departments, function(dept,deptId){
        remainingCombinations[branchId] = (remainingCombinations[branchId] || {});
        remainingCombinations[branchId][deptId] = {
          branchName: branch.name,
          departmentName: dept.name
        };
      });
    });
    
    _.each(service.simpleTimesheet, function(rowObj,rowKey){
      if (remainingCombinations[rowObj.branchId] != undefined) {
        delete remainingCombinations[rowObj.branchId][rowObj.departmentId];
        
        var size = 0;
        for (var key in remainingCombinations[rowObj.branchId]) {
          if (remainingCombinations[rowObj.branchId].hasOwnProperty(key)) size++;
        };
        if (size == 0){
          delete remainingCombinations[rowObj.branchId];
        };
      };
    });
    
    return remainingCombinations;
  };
  
  // Return a list of branches ready for ng-select
  // If a branch id is passed then the list only
  // returns the remaining [branch,department]
  // combinations on top of the branch passed
  service.branchDropDownList = function(specifiedBranchId, specifiedDeptId){
    var list = {};
    
    if (specifiedBranchId != undefined && specifiedDeptId != undefined) {
      _.each(service.remainingBranchDeptCombinations(), function(branchDepts, branchId){
        if (branchDepts[specifiedDeptId] != undefined) {
          list[branchId] = service.branches[branchId].name;
        };
      });
      list[specifiedBranchId] = service.branches[specifiedBranchId].name;
    } else {
      if (service.branches){
        _.each(service.branches, function(value,key){
          list[value.manual_id] = value.name;
        });
      };
    };
    
    return list;
  };
  
  // Return a list of departments ready for ng-select
  service.departmentDropDownList = function(specifiedBranchId, specifiedDeptId){
    var list = {};
    
    if (specifiedBranchId != undefined && specifiedDeptId != undefined) {
      var combinations = service.remainingBranchDeptCombinations();
      var branchDepts = combinations[specifiedBranchId];
      _.each(branchDepts, function(branchDept, deptId){
        list[deptId] = service.departments[deptId].name;
      });
      list[specifiedDeptId] = service.departments[specifiedDeptId].name;
    } else {
      if (service.departments){
        _.each(service.departments, function(value,key){
          list[value.manual_id] = value.name;
        });
      };
    };
    
    return list;
  };
  
  // Return wether there are remaining combinations available or not
  service.isBranchDeptPairAvailable = function() {
    var combinations = service.remainingBranchDeptCombinations();
    var size = 0;
    for (var key in combinations) {
      if (combinations.hasOwnProperty(key)) size++;
    };
    
    return (size > 0);
  }
  
  // Return the first available [branch, department] combination
  // available
  service.firstAvailableBranchDepartment = function() {
    var branchDeptPair = {};
    if (service.isBranchDeptPairAvailable()){
      var combinations = service.remainingBranchDeptCombinations();
      for (var branchId in combinations);
      for (var deptId in combinations[branchId]);
    
      branchDeptPair = {branchId: branchId, departmentId: deptId};
    };
    
    return branchDeptPair;
  }
  
  // Add a row with the first available [branch, department] pair
  service.quickAddRowToTimesheet = function() {
    if (service.simpleTimesheet && service.isBranchDeptPairAvailable()){
      var pair = service.firstAvailableBranchDepartment();
      var rowKey = service.initSimpleTimesheetRow(pair.branchId,pair.departmentId);
      service.temporaryAddedRows.push(rowKey)
      
      return true;
    }; 
    
    return false;
  };
  
  // Timetrex requires the punch time to be in format
  // "11:00 AM"
  service.formatDateToTxTime = function(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0'+minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    
    return strTime;
  };
  
  // Format a date to a key that looks like 20140516
  service.formatDateToKey = function(date) {
    var dateKey = date.getFullYear();
    dateKey += ((date.getMonth().toString().length == 1 ? '0' : '') + date.getMonth());
    dateKey += ((date.getDate().toString().length == 1 ? '0' : '') + date.getDate());
    
    return dateKey;
  };
  
  return service;
}]);

// Timesheet entity
module.factory('PunchEntity', [
'$http', '$cookies', '$q',
function($http, $cookies, $q) {
  var service = {};
  service.defaultPunch = undefined;
  
  
  service.create = function(data) {
    // First get the default punch or load it
    var qDefaultPunch;
    if (service.defaultPunch == undefined) {
      qDefaultPunch = service.loadDefaultPunch();
    } else {
      var deferred = $q.defer();
      qDefaultPunch = deferred.promise;
      deferred.resolve(service.defaultPunch);
    };
    
    // Perform the creation request
    var qCreation = $q.defer();
    qDefaultPunch.then(function(defaultPunch) {
      var punch = _.clone(defaultPunch);
      _.extend(punch,data);
      //console.log("Inside create - Punch to send");
      //console.log(punch);
      $http.get("/api/json/api.php",
      {
        params: {
          Class: "APIPunch",
          Method: "setPunch",
          SessionID: $cookies.SessionID,
          json: {0: punch }
        }
      }).then(function(data){
        qCreation.resolve(data);
      });
    });
    
    return qCreation.promise;
  }
  
  service.destroy = function(punchId) {
    var q = $http.get("/api/json/api.php",
    {
      params: {
        Class: "APIPunch",
        Method: "deletePunch",
        SessionID: $cookies.SessionID,
        json: {0: {0: punchId} }
      }
    });
    
    return q;
  }
  
  service.loadDefaultPunch = function() {
    var qLoad = $q.defer();
    
    $http.get("/api/json/api.php",
    {
      params: {
        Class: "APIPunch",
        Method: "getPunchDefaultData",
        SessionID: $cookies.SessionID
      }
    }).then(function(response){
      //console.log(response);
      service.defaultPunch = {};
      _.extend(service.defaultPunch,response.data);
      qLoad.resolve(service.defaultPunch);
    });
    
    return qLoad.promise;
  }
  
  return service;
}]);

// Timesheet entity
module.factory('PaystubEntity', [
'$http', '$cookies', '$q',
function($http, $cookies, $q) {
  var service = {};
  service.defaultPaystub = undefined;
  
  
  service.create = function(data) {
    // First get the default punch or load it
    var qDefaultPaystub;
    if (service.defaultPaystub == undefined) {
      qDefaultPaystub = service.loadDefaultPaystub();
    } else {
      var deferred = $q.defer();
      qDefaultPaystub = deferred.promise;
      deferred.resolve(service.defaultPaystub);
    };
    
    // Perform the creation request
    var qCreation = $q.defer();
    qDefaultPaystub.then(function(defaultPaystub) {
      var paystub = _.clone(defaultPaystub);
      _.extend(paystub,data);
      //console.log("Inside create - Paystub to send");
      //console.log(paystub);
      $http.get("/api/json/api.php",
      {
        params: {
          Class: "APIPayStubAmendment",
          Method: "setPayStubAmendment",
          SessionID: $cookies.SessionID,
          json: {0: paystub }
        }
      }).then(function(data){
        qCreation.resolve(data);
      });
    });
    
    return qCreation.promise;
  }
  
  service.destroy = function(punchId) {
    var q = $http.get("/api/json/api.php",
    {
      params: {
        Class: "APIPayStubAmendment",
        Method: "deletePayStubAmendment",
        SessionID: $cookies.SessionID,
        json: {0: {0: punchId} }
      }
    });
    
    return q;
  }
  
  service.loadDefaultPaystub = function() {
    var qLoad = $q.defer();
    
    $http.get("/api/json/api.php",
    {
      params: {
        Class: "APIPayStubAmendment",
        Method: "getPayStubAmendmentDefaultData",
        SessionID: $cookies.SessionID
      }
    }).then(function(response){
      //console.log(response);
      service.defaultPaystub = {};
      _.extend(service.defaultPaystub,response.data);
      qLoad.resolve(service.defaultPaystub);
    });
    
    return qLoad.promise;
  }
  
  return service;
}]);
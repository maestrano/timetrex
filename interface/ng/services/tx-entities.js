// This service is used to interact with Timetrex's backend
// It defines entities that can be retrieved and saved
var module = angular.module('tx.entities',[]);

// User entity
module.factory('UserEntity', [
'$http', '$cookies', '$q',
function($http, $cookies, $q) {
  var service = {};
  service.data = {};
  
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
        SessionID: $cookies.SessionID
      }
    }
    ).then(function(response){
      _.extend(service.data,response.data);
      qLoad.resolve(service.data);
    });
    
    return qLoad.promise;
  };
  
  return service;
}]);

// Timesheet entity
module.factory('TimesheetEntity', [
'$http', '$cookies', '$q', 'PunchEntity',
function($http, $cookies, $q, PunchEntity) {
  var service = {};
  service.data = {};
  service.branches = {};
  service.departments = {};
  service.currentDetails = {};
  service.temporaryAddedRows = [];
  
  // Load a user which is then accessible via
  // UserEntity.user
  // Return a promise
  service.load = function(userId,baseDate) {
    if (userId != undefined) {
      service.currentDetails.userId = userId
    } else {
      userId = service.currentDetails.userId
    };
    
    if (baseDate != undefined) {
      service.currentDetails.baseDate = baseDate
    } else {
      baseDate = service.currentDetails.baseDate
    };
    
    // Reset the temporary values
    service.temporaryAddedRows.length = 0;
    
    // Declare loading promise
    var qLoad = $q.defer();
    
    // Load timesheet data
    var q1 = $http.get("/api/json/api.php",
    {
      params: {
        Class: "APITimeSheet",
        Method: "getTimeSheetData",
        SessionID: $cookies.SessionID,
        json: {0: userId, 1: baseDate }
      }
    }
    ).then(function(response){
      _.extend(service.data,response.data);
      service.buildSimpleTimesheet();
      
    });
    
    // Load departments
    var q2 = $http.get("/api/json/api.php",
    {
      params: {
        Class: "APIDepartment",
        Method: "getDepartment",
        SessionID: $cookies.SessionID,
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
        SessionID: $cookies.SessionID,
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
      for (key in service.simpleTimesheet) {
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
    st[rowKey].departmentId = departmentId;
    st[rowKey].displayOrder = (st[rowKey].displayOrder || service.rowCount())
    st[rowKey].days = (st[rowKey].days || {});
    
    // Initialize dates
    var dateIterator = new Date(service.data.timesheet_dates.start_display_date);
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
  
  // Aggregate all punches by branch, department and day
  service.buildSimpleTimesheet = function() {
    service.simpleTimesheet = (service.simpleTimesheet || {});
    var simpleTimesheet = service.simpleTimesheet;
    
    // Reset Timesheet
    for (key in simpleTimesheet) {
      if (simpleTimesheet.hasOwnProperty(key)) {
        delete simpleTimesheet[key];
      };
    };
    
    // Initialize the default department
    service.initSimpleTimesheetRow(0,0);
    
    // We iterate through all punches and aggregate them in
    // branch > department > day
    // For each day we store the date in date format, the list
    // of punches related to it as well as the number of hours worked
    _.each(service.data.punch_data, function(punch) {
      // Initialize each day for this branch>department
      var rowKey = service.initSimpleTimesheetRow(punch.branch_id,punch.department_id);
      var rowObj = service.simpleTimesheet[rowKey];
      
      // Add punch to the right day and count hours
      var punchDate = new Date(punch.actual_time_stamp);
      var punchDateKey = service.formatDateToKey(punchDate);
      var dayObj = rowObj.days[punchDateKey];
      
      // Add punch hour if "Out" and substract it if "In"
      dayObj.hours += punchDate.getHours() * (punch.status == "In" ? -1 : 1)
      
      // Save the original number of hours
      dayObj.$origHours = dayObj.hours;
      
      // Add the punch to the list
      dayObj.punches = (dayObj.punches || []);
      dayObj.punches.push(punch);
    });
    
    return simpleTimesheet;
  };
  
  // Return the total hours for the timesheet
  service.timesheetTotals = function() {
    var totals = {};
    // Initialize dates
    var dateIterator = new Date(service.data.timesheet_dates.start_display_date);
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
  }
  
  // Check wether the timesheet is valid or not
  // Validation checks that a given day does not have
  // more than 24h work registered
  service.isTimesheetValid = function() {
    var isValid = true;
    
    _.each(service.timesheetTotals(), function(dayTotal,dayDateKey) {
      isValid = (isValid && dayTotal <= 24);
    });
    
    return isValid;
  }
  
  // Check wether the timesheet was changed or not
  service.isTimesheetChanged = function() {
    var isChanged = false;
    
    
    
    if (service.simpleTimesheet != undefined) {
      if (service.temporaryAddedRows.length > 0){
        isChanged = true;
      };
      
      isChanged = isChanged || service.isTimesheetChangedForSavePurpose();
    };
    
    return isChanged;
  }
  
  // Check wether the timesheet was changed or not
  service.isTimesheetChangedForSavePurpose = function() {
    var isChanged = false;
    
    if (service.simpleTimesheet != undefined) {
      _.each(service.simpleTimesheet, function(rowObj,rowKey) {
        _.each(rowObj.days, function(dayObject,dayDateKey) {
          isChanged = (isChanged || dayObject.hours != dayObject.$origHours);
        });
      });
    };
    
    return isChanged;
  }
  
  service.resetSimpleTimesheet = function() {
    if (service.simpleTimesheet != undefined) {
      _.each(service.temporaryAddedRows, function(rowKey){
        delete service.simpleTimesheet[rowKey];
      });
      
      _.each(service.simpleTimesheet, function(rowObj,rowKey) {
        _.each(rowObj.days, function(dayObject,dayDateKey) {
          dayObject.hours = dayObject.$origHours;
        });
      });
    }
    
    return true;
  }
  
  // Save the timesheet by going through the SimpleTimesheet
  // and deleting/creating punches when hours have changed
  service.saveSimpleTimesheet = function() {
    var actionPromises = [];
    
    _.each(service.simpleTimesheet, function(rowObj,rowKey) {
      _.each(rowObj.days, function(dayObj,dayDateKey) {
        console.log(dayObj);
        // Action is undertaken only if the number of
        // hours worked were changed for a given branch>department>day
        if (dayObj.hours != dayObj.$origHours) {
          // First create a promise for the local action
          // and add it to the array of promises
          var qLocalAction = $q.defer();
          actionPromises.push(qLocalAction.promise);
          
          // Then delete all punches for that branch>department>day
          var deletePromises = []
          _.each(dayObj.punches, function(punch){
            console.log("Deleting punch id: " + punch.id);
            deletePromises.push(PunchEntity.delete(punch.id));
          });
          
          if (dayObj.hours <= 0){
            qLocalAction.resolve([{},{}]);
          } else {
            // Once deleted create one PunchIn and one PunchOut
            // for that branch>department>day to reflect the 
            // number of hours worked
            // After creation of both punches we resolve the
            // qLocalAction promise
            $q.all(deletePromises).then(function(values){
              console.log(values);
              console.log(dayObj.date);
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
            
              console.log("Before create");
              console.log([punchInData,punchOutData])
            
              // Create punches then resolve locaAction promise
              PunchEntity.create(punchInData).then(function(value1){
                PunchEntity.create(punchOutData).then(function(value2){
                  console.log("After create");
                  console.log([value1,value2]);
                  qLocalAction.resolve([value1,value2]);
                });
              });
            });
          };
        };
      });
    });
    
    // Wait for the global combined promise to finish
    // then reload the timesheet
    var qFinal = $q.defer();
    $q.all(actionPromises).then(function(creationValues){
      service.load(service.currentDetails.userId,service.currentDetails.baseDate).then(function(value){
        qFinal.resolve(creationValues);
      });
    });
    
    // Return a global combined promise for all
    // actions
    return qFinal.promise;
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
        for (key in remainingCombinations[rowObj.branchId]) {
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
    for (key in combinations) {
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
      punch = _.clone(defaultPunch);
      _.extend(punch,data);
      console.log("Inside create - Punch to send");
      console.log(punch);
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
  
  service.delete = function(punchId) {
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
        SessionID: $cookies.SessionID,
      }
    }).then(function(response){
      console.log(response);
      service.defaultPunch = {};
      _.extend(service.defaultPunch,response.data);
      qLoad.resolve(service.defaultPunch);
    });
    
    return qLoad.promise;
  }
  
  return service;
}]);
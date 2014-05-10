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
  service.currentDetails = {};
  
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
    
    var qLoad = $q.defer();
    
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
      _.extend(service.data,response.data);
      service.buildSimpleTimesheet();
      qLoad.resolve(service.data);
    });
    
    
    
    return qLoad.promise;
  };
  
  // Build a new row in the simple timesheet if it does not
  // exist already
  // Return the new row
  service.initSimpleTimesheetRow = function(branchId,departmentId) {
    service.simpleTimesheet = (service.simpleTimesheet || {});
    var st = service.simpleTimesheet;
    
    // Initialize branch>department
    var branchDeptKey = branchId + '--' + departmentId;
    st[branchDeptKey] = (st[branchDeptKey] || {});
    st[branchDeptKey].branchId = branchId;
    st[branchDeptKey].departmentId = departmentId;
    st[branchDeptKey].days = (st[branchDeptKey].days || {});
    
    // Initialize dates
    var dateIterator = new Date(service.data.timesheet_dates.start_display_date);
    for (var i = 0; i < 7; i++) {
      var dateKey = service.formatDateToKey(dateIterator);
      st[branchDeptKey].days[dateKey] = (st[branchDeptKey].days[dateKey] || {});
      
      var branchDeptDay = st[branchDeptKey].days[dateKey];
      branchDeptDay.hours = (branchDeptDay.hours == undefined ? 0 : branchDeptDay.hours);
      branchDeptDay.$origHours = (branchDeptDay.$origHours == undefined ? 0 : branchDeptDay.$origHours);
      branchDeptDay.date = (new Date(dateIterator.toDateString()));
      
      // Move forward
      dateIterator.setDate(dateIterator.getDate() + 1);
    }
    
    return st[branchDeptKey];
  }
  
  // Aggregate all punches by branch, department and day
  service.buildSimpleTimesheet = function() {
    service.simpleTimesheet = (service.simpleTimesheet || {});
    var simpleTimesheet = service.simpleTimesheet;
    
    // Reset Timesheet
    _.each(simpleTimesheet,function(value,key){
      delete simpleTimesheet[key];
    });
    
    // Initialize the default department
    service.initSimpleTimesheetRow(0,0);
    
    // We iterate through all punches and aggregate them in
    // branch > department > day
    // For each day we store the date in date format, the list
    // of punches related to it as well as the number of hours worked
    _.each(service.data.punch_data, function(punch) {
      // Initialize each day for this branch>department
      var branchDeptObj = service.initSimpleTimesheetRow(punch.branch_id,punch.department_id);
      
      // Add punch to the right day and count hours
      var punchDate = new Date(punch.actual_time_stamp);
      var punchDateKey = service.formatDateToKey(punchDate);
      var dayObj = branchDeptObj.days[punchDateKey];
      
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
    
    _.each(service.simpleTimesheet, function(branchDeptObj,branchDeptKey) {
      _.each(branchDeptObj.days, function(dayObject,dayDateKey) {
        totals[dayDateKey] += dayObject.hours;
      });
    });
    
    return totals;
  }
  
  service.isTimesheetChanged = function() {
    var isChanged = false;
    
    if (service.simpleTimesheet != undefined) {
      _.each(service.simpleTimesheet, function(branchDeptObj,branchDeptKey) {
        _.each(branchDeptObj.days, function(dayObject,dayDateKey) {
          isChanged = (isChanged || dayObject.hours != dayObject.$origHours);
        });
      });
    }
    
    return isChanged;
  }
  
  service.resetSimpleTimesheet = function() {
    if (service.simpleTimesheet != undefined) {
      _.each(service.simpleTimesheet, function(branchDeptObj,branchDeptKey) {
        _.each(branchDeptObj.days, function(dayObject,dayDateKey) {
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
    
    _.each(service.simpleTimesheet, function(branchDeptObj,branchDeptKey) {
      _.each(branchDeptObj.days, function(dayObj,dayDateKey) {
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
          
          // Once deleted create one PunchIn and one PunchOut
          // for that branch>department>day to reflect the 
          // number of hours worked
          // After creation of both punches we resolve the
          // qLocalAction promise
          $q.all(deletePromises).then(function(values){
            console.log(values);
            console.log(dayObj.date);
            var punchInData = {
              department_id: branchDeptObj.departmentId,
              branch_id: branchDeptObj.branchId,
              time_stamp: dayObj.date.toLocaleString(),
              punch_time: "12:00 AM",
              status_id: 10,
              status: "In"
            };
            
            var punchOutDate = new Date(dayObj.date);
            punchOutDate.setHours(punchOutDate.getHours()+dayObj.hours);
            var punchOutData = {
              department_id: branchDeptObj.departmentId,
              branch_id: branchDeptObj.branchId,
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
            // $q.all([PunchEntity.create(punchInData), PunchEntity.create(punchOutData)]).then(function(values){
            //   console.log("After create");
            //   console.log(values);
            //   qLocalAction.resolve(values);
            // });
          });
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
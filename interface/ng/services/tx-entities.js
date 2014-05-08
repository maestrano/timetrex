// This service is used to interact with Timetrex's backend
// It defines entities that can be retrieved and saved
var module = angular.module('tx.entities',[]);

// User entity
module.factory('UserEntity', [
'$http', '$cookies',
function($http, $cookies) {
  var service = {};
  service.data = {};
  
  // Load a user which is then accessible via
  // UserEntity.user
  // Return a promise
  service.load = function() {
    var q = $http.get("/api/json/api.php",
    { 
      params: {
        Class: "APIAuthentication",
        Method: "getCurrentUser",
        SessionID: $cookies.SessionID
      }
    }
    );
    
    q.then(function(response){
      _.extend(service.data,response.data);
    });
    
    return q;
  };
  
  return service;
}]);

// Timesheet entity
module.factory('TimesheetEntity', [
'$http', '$cookies',
function($http, $cookies) {
  var service = {};
  service.data = {};
  
  // Load a user which is then accessible via
  // UserEntity.user
  // Return a promise
  service.load = function(userId,baseDate) {
    var q = $http.get("/api/json/api.php",
    {
      params: {
        Class: "APITimeSheet",
        Method: "getTimeSheetData",
        SessionID: $cookies.SessionID,
        json: {0: userId, 1: baseDate }
      }
    }
    );
    
    q.then(function(response){
      _.extend(service.data,response.data[0]);
    });
    
    
    
    return q;
  };
  
  // Aggregate all punches by branch, department and day
  service.buildSimpleTimesheet = function() {
    var simpleTimesheet = {};
    
    // We iterate through all punches and aggregate them in
    // branch > department > day
    // For each day we store the date in date format, the list
    // of punches related to it as well as the number of hours worked
    _.each(service.punch_data, function(punch) {
      simpleTimesheet[punch.branch_id] = simpleTimesheet[punch.branch_id] || {};
      simpleTimesheet[punch.branch_id][punch.department_id] = simpleTimesheet[punch.branch_id][punch.department_id] || {};
      
      var punchDate = new Date(punch.actual_time_stamp);
      var punchDateString = punchDate.toDateString();
      simpleTimesheet[punch.branch_id][punch.department_id][punchDateString] = simpleTimesheet[punch.branch_id][punch.department_id][punchDateString] || {};
      var dayObj = simpleTimesheet[punch.branch_id][punch.department_id][punchDateString];
      
      // Initialize the day object
      dayObj.date = (new Date(punchDateString));
      dayObj.hours = (dayObj.hours == undefined ? 0 : dayObj.hours)
      
      // Add punch hour if "Out" and substract it if "In"
      dayObj.hours += punchDate.getHours() * (punch.status == "In" ? -1 : 1)
      
      // Add the punch to the list
      dayObj.punches = [];
      dayObj.punches.push(punch);
    });
    
    service.simpleTimesheet = simpleTimesheet;
    
    return simpleTimesheet;
  };
  
  
  return service;
}]);

// Timesheet entity
module.factory('PunchEntity', [
'$http', '$cookies',
function($http, $cookies) {
  var service = {};
  service.defaultPunch = {};
  
  
  service.delete = function(punchId) {
    var q = $http.get("/api/json/api.php",
    {
      params: {
        Class: "APIPunch",
        Method: "deletePunch",
        SessionID: $cookies.SessionID,
        json: {0: {0: punchId} }
      }
    }
    );
    
    return q;
  }
}
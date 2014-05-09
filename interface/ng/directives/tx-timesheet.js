var module = angular.module('tx.timesheet',[]);

// Timesheet directive controller
module.controller('TimesheetCtrl',[
'$scope', '$q', 'UserEntity', 'TimesheetEntity',
function($scope,$q,UserEntity,TimesheetEntity){
  //----------------------------------
  // Initialize values
  //----------------------------------
  var currentDate = new Date();
  var qDataLoading = $q.defer();
  $scope.isLoading = true;
  
  //----------------------------------
  // Load data
  //----------------------------------
  UserEntity.load().then(function(value){
    TimesheetEntity.load(UserEntity.data.id,currentDate).then(function(value){
      $scope.timesheetHeader = []
      var startDate = new Date(timesheet_dates.start_display_date)
      for (var i = 0; i < 7; i++) {
        text += "The number is " + i + "<br>";
      }
      qDataLoading.resolve(value);
    });
  });
  
  //----------------------------------
  // Display view once data is loaded
  //----------------------------------
  qDataLoading.promise.then(function(value){
    $scope.isLoading = false;
    $scope.timesheet = TimesheetEntity.simpleTimesheet;
    
  });
  
  
  //$scope.timesheetMessage = "Hello from scope";
  
}]);

// Timesheet directive template
module.directive('txTimesheet', [function(){
  return {
    restrict: 'A',
    scope: {
      weekNumber: '@'
    },
    templateUrl: "/interface/ng/templates/tx-timesheet.html",
    controller: 'TimesheetCtrl'
  };
}]);

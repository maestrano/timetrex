var module = angular.module('tx.timesheet',[]);

// Timesheet directive controller
module.controller('TimesheetCtrl',[
'$scope', 
function($scope){
  $scope.timesheetMessage = "Hello from scope";
  
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

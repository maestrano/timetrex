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
      console.log(TimesheetEntity);
      var dateIterator = new Date(TimesheetEntity.data.timesheet_dates.start_display_date);
      
      $scope.timesheetStartDate = new Date(dateIterator);
      for (var i = 0; i < 7; i++) {
        $scope.timesheetHeader.push(new Date(dateIterator));
        dateIterator.setDate(dateIterator.getDate() + 1);
      }
      dateIterator.setDate(dateIterator.getDate() - 1);
      $scope.timesheetEndDate = new Date(dateIterator);
      
      qDataLoading.resolve(value);
    });
  });
  
  //----------------------------------
  // Helpers
  //----------------------------------
  $scope.helper = helper = {};
  helper.statuses = {};
  helper.statuses.isSaving = false;
  
  helper.isSuccessBtnEnabled = function(){
    return TimesheetEntity.isTimesheetChanged();
  };
  
  helper.isSuccessBtnShown = function(){
    return !helper.statuses.isSaving;
  };
  
  helper.isCancelBtnShown = function(){
    return (!helper.statuses.isSaving && TimesheetEntity.isTimesheetChanged());
  };
  
  helper.isSaveLoaderShown = function(){
    return helper.statuses.isSaving;
  };
  
  helper.performSave = function() {
    helper.statuses.isSaving = true;
    TimesheetEntity.saveSimpleTimesheet().then(function(value){
      helper.statuses.isSaving = false;
    });
  }
  
  helper.performCancel = function() {
    TimesheetEntity.resetSimpleTimesheet();
  }
  
  //----------------------------------
  // Display view once data is loaded
  //----------------------------------
  qDataLoading.promise.then(function(value){
    $scope.currentDate = currentDate;
    $scope.timesheet = TimesheetEntity.simpleTimesheet;
    $scope.timesheetTotals = TimesheetEntity.timesheetTotals //function
    
    $scope.isLoading = false;
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

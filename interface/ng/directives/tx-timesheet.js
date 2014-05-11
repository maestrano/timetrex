var module = angular.module('tx.timesheet',[]);

// Custom filter to sort objects in ng-repeat
module.filter('orderObjectBy', function() {
  return function(items, field, reverse) {
    var filtered = [];
    angular.forEach(items, function(item) {
      filtered.push(item);
    });
    filtered.sort(function (a, b) {
      return (a[field] > b[field] ? 1 : -1);
    });
    if(reverse) filtered.reverse();
    return filtered;
  };
});

// Timesheet directive controller
module.controller('TimesheetCtrl',[
'$scope', '$q', 'UserEntity', 'TimesheetEntity',
function($scope,$q,UserEntity,TimesheetEntity){
  //----------------------------------
  // Initialize values
  //----------------------------------
  var currentDate = new Date();
  currentDate.setDate(currentDate.getDate() - 1);
  
  //----------------------------------
  // Helpers
  //----------------------------------
  $scope.helper = helper = {};
  helper.currentDate = new Date();
  helper.currentDate.setDate(helper.currentDate.getDate() - 1); 
  
  helper.statuses = {};
  helper.isUserLoading = true;
  helper.isTimesheetLoading = true;
  helper.statuses.isSaving = false;
  
  helper.reloadTimesheet = function(){
    helper.isTimesheetLoading = true;
    
    TimesheetEntity.load(UserEntity.data.id,currentDate).then(function(value){
      // Build table structure
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
      
      // Load scope
      $scope.user = UserEntity.data;
      console.log($scope.user);
      $scope.timesheet = TimesheetEntity.simpleTimesheet;
      $scope.timesheetTotals = TimesheetEntity.timesheetTotals //function
      $scope.branchDropdown = TimesheetEntity.branchDropDownList //function
      $scope.departmentDropdown = TimesheetEntity.departmentDropDownList //function
      
      helper.isTimesheetLoading = false;
    });
  };
  
  helper.isSuccessBtnEnabled = function(){
    return TimesheetEntity.isTimesheetChangedForSavePurpose() && TimesheetEntity.isTimesheetValid();
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
  };
  
  helper.performCancel = function() {
    TimesheetEntity.resetSimpleTimesheet();
  };
  
  helper.isAddRowBtnShown = function() {
    return TimesheetEntity.isBranchDeptPairAvailable();
  };
  
  helper.performAddRow = function() {
    TimesheetEntity.quickAddRowToTimesheet();
  };
  
  helper.dayCellStyle = function(hours) {
    if (hours < 0) return {color: 'red'};
    if (hours == 0) return {color:'#bbbbbb'};
    return {};
  };
  
  helper.performLogout = function() {
    console.log("logout");
    UserEntity.logout();
  };
  
  helper.performPreviousPeriod = function(){
    
  };
  
  //----------------------------------
  // Display view once data is loaded
  //----------------------------------
  UserEntity.load().then(function(value){
    helper.isUserLoading = false;
    helper.reloadTimesheet();
  });
  
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

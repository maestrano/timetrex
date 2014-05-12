"use strict";

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
  // Helpers
  //----------------------------------
  $scope.timesheetHeader = [];
  var helper = $scope.helper = {};
  helper.currentDate = new Date();
  //helper.currentDate.setDate(helper.currentDate.getDate() - 1); 
  
  helper.statuses = {};
  helper.isUserLoading = true;
  helper.isTimesheetLoading = true;
  helper.statuses.isSaving = false;
  
  helper.reloadTimesheet = function(){
    helper.isTimesheetLoading = true;
    
    // Need to disable this method temporarily to avoid
    // digest loop
    $scope.timesheetTotals = function(){ return false; };
    
    TimesheetEntity.load(UserEntity.data.id,helper.currentDate).then(function(value){
      // Build table structure
      $scope.timesheetHeader.length = 0;
      //console.log(TimesheetEntity.data.timesheet_dates);
      var dateIterator = moment(TimesheetEntity.data.timesheet_dates.start_date,"X").toDate();
      
      $scope.timesheetStartDate = new Date(dateIterator);
      for (var i = 0; i < 7; i++) {
        //console.log(dateIterator.getFullYear());
        $scope.timesheetHeader.push(new Date(dateIterator));
        dateIterator.setDate(dateIterator.getDate() + 1);
      };
      dateIterator.setDate(dateIterator.getDate() - 1);
      $scope.timesheetEndDate = new Date(dateIterator);
      
      
      // Load scope
      $scope.timesheet = TimesheetEntity.simpleTimesheet;
      $scope.simpleZonesheet = TimesheetEntity.simpleZonesheet;
      $scope.timesheetTotals = TimesheetEntity.timesheetTotals; //function
      $scope.branchDropdown = TimesheetEntity.branchDropDownList; //function
      $scope.departmentDropdown = TimesheetEntity.departmentDropDownList; //function
      //console.log($scope.timesheetHeader);
      helper.isTimesheetLoading = false;
    });
  };
  
  helper.isSuccessBtnEnabled = function(){
    return TimesheetEntity.isGlobalTimesheetChangedForSavePurpose() && TimesheetEntity.isGlobalTimesheetValid();
  };
  
  helper.isSuccessBtnShown = function(){
    return !helper.statuses.isSaving;
  };
  
  helper.isCancelBtnShown = function(){
    return (!helper.statuses.isSaving && TimesheetEntity.isGlobalTimesheetChanged());
  };
  
  helper.isSaveLoaderShown = function(){
    return helper.statuses.isSaving;
  };
  
  helper.performSave = function() {
    helper.statuses.isSaving = true;
    TimesheetEntity.saveGlobalTimesheet().then(function(value){
      helper.statuses.isSaving = false;
    });
  };
  
  helper.performCancel = function() {
    TimesheetEntity.resetGlobalTimesheet();
  };
  
  helper.isAddRowBtnShown = function() {
    return TimesheetEntity.isBranchDeptPairAvailable();
  };
  
  helper.performAddRow = function() {
    TimesheetEntity.quickAddRowToTimesheet();
  };
  
  helper.inputCellStyle = function(number) {
    if (number < 0) return {color: 'red'};
    if (number == 0) return {color:'#bbbbbb'};
    return {};
  };
  
  helper.inputCellEnabled = function() {
    return !helper.statuses.isSaving;
  };
  
  helper.performLogout = function() {
    //console.log("logout");
    UserEntity.logout();
  };
  
  helper.performPreviousPeriod = function(){
    helper.currentDate.setDate(helper.currentDate.getDate() - 7);
    helper.reloadTimesheet();
  };
  
  helper.performNextPeriod = function(){
    helper.currentDate.setDate(helper.currentDate.getDate() + 7);
    helper.reloadTimesheet();
  };
  
  helper.isTimesheetEmpty = function(){
    return (TimesheetEntity.rowCount() == 0);
  }
  
  //----------------------------------
  // Display view once data is loaded
  //----------------------------------
  UserEntity.load().then(function(value){
    $scope.user = UserEntity.data;
    //console.log($scope.user);
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

// Angular app declaration

// Declare module
this.timetrexApp = angular.module('timetrex',[
  'ngCookies',
  'ngAnimate',
  'tx.timesheet',
  'tx.entities'
]);

// Configure the http headers for AJAX requests
// Define content type and set csrf token
this.timetrexApp.config(['$httpProvider', function($httpProvider) {
  $httpProvider.defaults.headers.post['Content-Type'] = 'application/json';
  $httpProvider.defaults.headers.put['Content-Type'] = 'application/json';
  $httpProvider.defaults.headers.delete = {};
  $httpProvider.defaults.headers.delete['Content-Type'] = 'application/json';
  
  return $httpProvider;
}]);
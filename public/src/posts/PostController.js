(function(){

  angular
       .module('posts', ['ngMaterial'])
       .controller('PostController', [
          '$scope', '$http', '$sce', '$location', '$anchorScroll',
          PostController
       ]);

  /**
   * Main Controller for the Angular Material Starter App
   * @param $scope
   * @param $http
   * @param $sce
 * @constructor
   */
  function PostController($scope, $http, $sce, $location, $anchorScroll) {
    var self = this;

      $scope.loading = 1;

      $scope.q = '';

      $scope.not = 0;

      $scope.nl = false;
      $scope.stack = false;

      var current_page = 1;

      $http.get('/api/search').success(function(data) {
          $scope.posts = data;
          $scope.loading = 0;
      });

      $scope.highlight = function(haystack) {
          needle = $scope.q;
          if(!needle) {
              return $sce.trustAsHtml(haystack);
          }
          return $sce.trustAsHtml(haystack.replace(new RegExp(needle, "gi"), function(match) {
              return '<span class="highlightedText">' + match + '</span>';
          }));
      };

      $scope.search = function() {
          $scope.loading = 1;
          $http.get('/api/search?q='+$scope.q+'&nl='+$scope.nl+'&stack='+$scope.stack).success(function(data) {
              $scope.posts = data;
              $scope.loading = 0;
              if(data.error == 1) {
                  $scope.not = 1;
              } else {
                  $scope.not = 0;
              }
          });
      }

      $scope.next = function(type) {
          $scope.loading = 1;
          var next = current_page + 1;
          $http.get('/api/search?q='+$scope.q+'&nl='+$scope.nl+'&stack='+$scope.stack+'&page='+next).success(function(data) {
              $scope.posts = data;
              current_page = data.info.current_page;
              $scope.loading = 0;

              if(data.error == 1) {
                  $scope.not = 1;
              } else {
                  $scope.not = 0;
              }
          });
          // set the location.hash to the id of
          // the element you wish to scroll to.
          $location.hash('search');

          // call $anchorScroll()
          $anchorScroll();
      }

      $scope.prev = function(type) {
          $scope.loading = 1;
          var prev = current_page -1;
          $http.get('/api/search?q='+$scope.q+'&nl='+$scope.nl+'&stack='+$scope.stack+'&page='+ prev).success(function(data) {
              $scope.posts = data;
              current_page = data.info.current_page;
              $scope.loading = 0;

              if(data.error == 1) {
                  $scope.not = 1;
              } else {
                  $scope.not = 0;
              }
          });
          // set the location.hash to the id of
          // the element you wish to scroll to.
          $location.hash('search');

          // call $anchorScroll()
          $anchorScroll();
      }


  }

})();

(function(){

  angular
       .module('posts', ['ngMaterial'])
       .controller('PostController', [
          '$scope', '$http', '$sce',
          PostController
       ]);

  /**
   * Main Controller for the Angular Material Starter App
   * @param $scope
   * @param $http
   * @param $sce
 * @constructor
   */
  function PostController($scope, $http, $sce) {
    var self = this;

      $scope.loading = 1;

      $scope.q = '';

      $scope.nl = false;
      $scope.stack = false;

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
          });
      }


  }

})();

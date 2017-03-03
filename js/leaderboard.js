document.write('<style>.nojs{display:none;} .js{display:initial;}</style>');

var app = angular.module('leaderboardApp', ['smart-table']);

app.controller('mainController', ['$scope', '$http', '$interval', function ($scope, $http, $interval) {
    $scope.sortType = 'totalconnections';
    $scope.sortReverse = true;
    $scope.searchClients = '';
    $scope.selectedClient = { show: false };

    function getData() {
        $http.get('api/clientlist.php').then(function successCallback(response) {
            $scope.clientlist = response.data;
        }, function failureCallback(reason) {
            console.log('request failed: ' + reason);
        });
    }

    getData();
    $interval(getData, 30000)

    $scope.showUserInfo = function (dbid) {
        $http({
            method: 'POST',
            url: 'api/userinfo.php',
            data: 'dbid=' + dbid,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).then(function successCallback(response) {
            $scope.selectedClient = response.data;
            $scope.selectedClient.show = true;
        }, function failureCallback(reason) {
            console.log('request failed: ' + reason);
        });
    };

    $scope.setSortType = function ($event, sortType) {
        $event.preventDefault();

        if ($scope.sortType == sortType) {
            $scope.sortReverse = !$scope.sortReverse;
        } else {
            $scope.sortType = sortType;
        }
    }

    $scope.getSortIcon = function (sortType) {
        return $scope.sortType == sortType ? 'fa fa-caret-' + ($scope.sortReverse ? 'up' : 'down') : '';
    }

    $scope.getStatusIcon = function (client) {
        return client.away ? 'fa fa-check-circle away' : client.online ? 'fa fa-check-circle online' : '';
    }

    $scope.getStatus = function (client) {
        return client.away ? 'away' : client.online ? 'online' : '';
    }

    $scope.getAvatar = function (client) {
        // return avatar or empty image
        return 'data:image/' + (client.avatar ? 'png;base64,' + client.avatar : 'gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
    }
}]);
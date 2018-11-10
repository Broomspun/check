var app = angular.module("ShopstyleApp", []);
app.controller("brandCtrl", function ($scope,$http) {
    $http.get('http://api.shopstyle.com/api/v2/brands?pid=uid224-39609668-69')
        .then(function (res) {
            $scope.users = res.data.brands;
            $scope.items = res.data.brands.length;
        });
    $scope.toggleCustomClass = function ($event) {
        if(angular.element($event.currentTarget).hasClass('active'))
            angular.element($event.currentTarget).removeClass('active');
        else
            angular.element($event.currentTarget).addClass('active');

        angular.element('#shopstyle-filter-wrap ul.shopstyle-cats li.active').trigger('click')
    }
});


app.controller("retailerCtrl", function ($scope,$http) {
    $http.get('http://api.shopstyle.com/api/v2/retailers?pid=uid224-39609668-69')
        .then(function (res) {
            $scope.retailers = res.data.retailers;
            $scope.items = res.data.retailers.length;
        });
    $scope.toggleCustomClass = function ($event) {
        if(angular.element($event.currentTarget).hasClass('active'))
            angular.element($event.currentTarget).removeClass('active');
        else
            angular.element($event.currentTarget).addClass('active');

        angular.element('#shopstyle-filter-wrap ul.shopstyle-cats li.active').trigger('click')
    }
});

app.filter('orderByScore', function(){
    return function(input, attribute) {
        if (!angular.isObject(input)) return input;

        var array = [];
        for(var objectKey in input) {
            array.push(input[objectKey]);
        }

        array.sort(function(a, b){
            a = a[attribute];
            b = b[attribute];
            return b - a;
        });
        return array;
    }
});

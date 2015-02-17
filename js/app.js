/*
global
angular: true,
setTimeout: true,
window: true,
$: true
*/

'use strict';

var app = angular.module("entitlementAdmin", ["ngGrid", "ui.bootstrap", "ui.keypress"]);

app.directive("resizableGrid", function($window) {
	return function($scope) {
		var $el = arguments[2].$$element;
		$scope.resizeHandler = function() {
			$el.height(window.innerHeight - $el.offset().top - 20);
			$el.width(1160);
		};

		angular.element($window).bind("resize", function() {
			$scope.resizeHandler();
		});

		// Handler for when a user selects a tab.
		// If a user resizes when the tab is not selected then the height will not be correct since top will
		// be zero when the tab is not selected so adjust the height when the tab is selected.
		$scope.$on("tabSelected", function() {
			// Need a delay otherwise window.innerHeight will not be properly reflected.
			setTimeout(function() {
				$(window).trigger("resize");
			}, 1);
		});

		$scope.$on("loginSuccess", function() {
			// Need a delay otherwise window.innerHeight will not be properly reflected.
			setTimeout(function() {
				$(window).trigger("resize");
			}, 1);
		});

		// Need to trigger the resize event rather than calling $scope.resizeHandler() so the grids resize properly.
		$(window).trigger("resize");
	};
});
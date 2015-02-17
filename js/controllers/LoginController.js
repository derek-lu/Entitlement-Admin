// Controller for the login dialog.
app.controller("LoginController", function($scope, loginService) {
	$scope.isInvalidLogin = false;

	$scope.submit = function() {
		$scope.loginForm.submitted = $scope.isInvalidLogin = false;

		$scope.loginErrorMessage = "";
		if ($scope.loginForm.adobeId.$invalid || $scope.loginForm.password.$invalid) { // Make sure the fields are not empty.
			$scope.loginErrorMessage = "All fields are required.";
		} else if ($scope.loginForm.$valid) {
			$scope.isValidating = true;
			loginService.login($scope.adobeId, $scope.password).then(
				function(data) {
					$scope.isValidating = false;
					$scope.isUserLoggedIn = data.success;
					if (!data.success) {
						$scope.loginErrorMessage = data.info;
					} else {
						$scope.guid = data.guid;
						$scope.server = data.server;
						$scope.ticket = data.ticket;
						$scope.$broadcast("loginSuccess");
					}
				}
			);
		} else {
			$scope.loginForm.submitted = true;
		}
	}
});
// Controller for the Add User dialog.
var AddUserDialogController = function ($scope, $modalInstance, entitlementService, guid) {
	// Data storage for the user name and description.
	$scope.form = {};

	$scope.ok_clickHandler = function () {
		if (!$scope.form.name) { // Make sure the fields are not empty.
			$scope.form.errorMessage = "Please enter a user name.";
		} else if (!$scope.form.password1) {
			$scope.form.errorMessage = "Please enter a password.";
		} else if ($scope.form.password1 != $scope.form.password2) {
			$scope.form.errorMessage = "Please verify that your passwords match.";
		} else {
			$scope.form.isAddingUser = true;

			entitlementService.addUser(guid, $scope.form.name, $scope.form.password1, $scope.form.description).then(
				function(data) {
					$scope.form.isAddingUser = false;
					if (data.success) {
						// Assign the user id returned from the request.
						$scope.form.id = data.id;
						$modalInstance.close($scope.form);
					} else {
						$scope.form.errorMessage = data.description || "Sorry, unable to add this user.";
					}
				},
				function() {
					$scope.form.isAddingUser = false;
					$scope.form.errorMessage = "Sorry, unable to reach the database.";
				}
			);
		}
	};

	$scope.cancel_clickHandler = function () {
		$modalInstance.dismiss("cancel");
	};
};
// Controller for the Add Group dialog.
var AddGroupDialogController = function ($scope, $modalInstance, entitlementService, guid) {
	// Placeholder for ie. 
	$scope.partialInitHandler = function() {
		$("input").placeholder(); 
	}

	// Data storage for the group name and description.
	$scope.form = {};

	$scope.ok_clickHandler = function () {
		if (!$scope.form.name) { // Make sure the fields are not empty.
			$scope.form.errorMessage = "Please enter a group name.";
		} else {
			$scope.form.isAddingGroup = true;

			entitlementService.addGroup(guid, $scope.form.name, $scope.form.description).then(
				function(data) {
					$scope.form.isAddingGroup = false;
					if (data.success) {
						// Assign the group id returned from the request.
						$scope.form.id = data.id;
						$modalInstance.close($scope.form);
					} else {
						$scope.form.errorMessage = data.description || "Sorry, unable to add this group.";
					}
				},
				function() {
					$scope.form.isAddingGroup = false;
					$scope.form.errorMessage = "Sorry, unable to reach the database.";
				}
			);
		}
	};

	$scope.cancel_clickHandler = function () {
		$modalInstance.dismiss("cancel");
	};
};

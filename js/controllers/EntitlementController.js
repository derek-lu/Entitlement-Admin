// Main controller for the app.
app.controller("EntitlementController", ["$scope", "entitlementService", "$modal", function($scope, entitlementService, $modal) {
	// Triggered when a user successfully logs in.
	$scope.$on("loginSuccess", function(e) {
		// Get the published folios for the guid.
		$scope.getFolios();

		// Get the app id for this guid.
		entitlementService.getAppId($scope.guid).then(function(data) {
			$scope.appId = data.appId;
		});

		// Get the users for this guid.
		entitlementService.getUsers($scope.guid).then(function(data) {
			$scope.users = data.users;
		});

		// Get the groups for this guid.
		entitlementService.getGroups($scope.guid).then(function(data) {
			$scope.groups = data.groups;
		});
	});

	$scope.getFolios = function() {
		entitlementService.getFolios($scope.guid).then(function(folios) {
			$scope.folios = folios;
			$rootScope.$emit("getFolios", folios);

			// Create a timeout to download the list of folios to detect new/removed folios every 30 secs.
			$timeout(function() {
				$scope.getFolios();
			}, 30000)
		});
	}

	var href = location.href;
	$scope.serviceURL = location.href + (href.lastIndexOf("/") == href.length - 1 ? "" : "/") + "services";

	$scope.groupGridOptions = {
		data: "groups",
		multiSelect: false,
		afterSelectionChange: function(rowItem, e) {
			$scope.groupSelectHandler(rowItem);
		},
		 columnDefs: [
		 	{displayName: "Name", field: "name"},
		 	{displayName: "Description", field: "description"}
		 ]
	};

	$scope.userGridOptions = {
		data: "users",
		multiSelect: false,
		afterSelectionChange: function(rowItem, e) {
			$scope.userSelectHandler(rowItem);
		},
		 columnDefs: [
		 	{displayName: "Name", field: "name"},
		 	{displayName: "Description", field: "description"}
		 ]
	};

	$scope.folioGridOptions = {
		data: "folios",
		multiSelect: false,
		afterSelectionChange: function(rowItem, e) {
			$scope.folioSelectHandler(rowItem);
		},
		columnDefs: [
			{displayName: "Pub Date", field: "publicationDate", cellFilter: "date", width: 120},
			{displayName: "Publication Name", field: "magazineTitle"},
			{displayName: "Folio Number", field: "issueNumber"},
			{displayName: "Product ID", field: "productId"}
		]
	};

	$scope.logoutButton_clickHandler = function() {
		location.reload();
	}

	// User double-clicked a group from the grid.
	$scope.groupSelectHandler = function(rowItem) {
		$scope.selectedGroup = rowItem.entity;

		// Figure out if a user double-clicked the row.
		if (Date.now() - $scope.firstGroupClickTime < 500 // Check to see if the second click was within 500ms
			&& $scope.selectedGroup == $scope.previousSelectedGroup) { // Make sure the user clicked the same row.
			$scope.displayEditGroupDialog();
		}

		$scope.firstGroupClickTime = Date.now();
		$scope.previousSelectedGroup = rowItem.entity;
	}

	$scope.displayEditGroupDialog = function() {
		var modalInstance = $modal.open({
			templateUrl: "js/partials/EditGroupDialog.html",
			controller: EditGroupDialogController,
			size: "sm",
			resolve: {
				entitlementService: function() { return entitlementService },
				group: function() { return $scope.selectedGroup },
				folios: function() { return $scope.folios },
				users: function() { return $scope.users }
			}
		});
	}

	$scope.userSelectHandler = function(rowItem) {
		$scope.selectedUser = rowItem.entity;

		// Figure out if a user double-clicked the row.
		if (Date.now() - $scope.firstUserClickTime < 500 // Check to see if the second click was within 500ms
			&& $scope.selectedUser == $scope.previousSelectedUser) { // Make sure the user clicked the same row.
			$scope.displayEditUserDialog();
		}

		$scope.firstUserClickTime = Date.now();
		$scope.previousSelectedUser = rowItem.entity;
	}

	$scope.displayEditUserDialog = function() {
		var modalInstance = $modal.open({
			templateUrl: "js/partials/EditUserDialog.html",
			controller: EditUserDialogController,
			size: "sm",
			resolve: {
				entitlementService: function() { return entitlementService },
				user: function() { return $scope.selectedUser },
				folios: function() { return $scope.folios },
				groups: function() { return $scope.groups },
				guid: function() { return $scope.guid }
			}
		});
	}

	// User double-clicked a folio from the grid.
	$scope.folioSelectHandler = function(rowItem) {
		$scope.selectedFolio = rowItem.entity;

		// Figure out if a user double-clicked the row.
		if (Date.now() - $scope.firstFolioClickTime < 500 // Check to see if the second click was within 500ms
			&& $scope.selectedFolio == $scope.previousSelectedFolio) { // Make sure the user clicked the same row.
			$scope.displayEditFolioDialog();
		}

		$scope.firstFolioClickTime = Date.now();
		$scope.previousSelectedFolio = rowItem.entity;
	}

	$scope.displayEditFolioDialog = function() {
		var modalInstance = $modal.open({
		templateUrl: "js/partials/EditFolioDialog.html",
		controller: EditFolioDialogController,
		size: "sm",
		resolve: {
				entitlementService: function() { return entitlementService },
				folio: function() { return $scope.selectedFolio },
				groups: function() { return $scope.groups },
				users: function() { return $scope.users },
				guid: function() { return $scope.guid }
			}
		});
	}

	$scope.appIdInput_changeHandler = function() {
		$scope.appIdErrorMessage = "";
		// Submit the change after 2 seconds of inactivity in the text field.
		clearTimeout($scope.changeTimeout);
		$scope.changeTimeout = setTimeout($scope.setAppId, 2000);
	}

	$scope.setAppId = function() {
		entitlementService.setAppId($scope.guid, $scope.appId).then(
			function(data) {
				if (!data.success)
					$scope.appIdErrorMessage = data.description;
			}
		);
	}

	$scope.displayAddGroupDialog = function() {
		var modalInstance = $modal.open({
			templateUrl: "js/partials/AddGroupDialog.html",
			controller: AddGroupDialogController,
			size: "sm",
			resolve: {
				entitlementService: function() { return entitlementService },
				guid: function() { return $scope.guid }
			}
		});

		modalInstance.result.then(
			function(data) { // Handler for when a user clicks "ok".
				$scope.groups.push({guid: $scope.guid, id: data.id, name: data.name, description: data.description});
			}
		);
	}

	$scope.displayAddUserDialog = function() {
		var modalInstance = $modal.open({
			templateUrl: "js/partials/AddUserDialog.html",
			controller: AddUserDialogController,
			size: "sm",
			resolve: {
				entitlementService: function() { return entitlementService },
				groups: function() { return $scope.groups },
				guid: function() { return $scope.guid }
			}
		});

		modalInstance.result.then(
			function(data) { // Handler for when a user clicks "ok".
				$scope.users.push({guid: $scope.guid, id: data.id, name: data.name, description: data.description});
			}
		);
	}

	$scope.deleteGroup = function() {
		if ($scope.selectedGroup && !$scope.isDeletingGroup) {
			$scope.isDeletingGroup = true;
			entitlementService.deleteGroup($scope.selectedGroup).then(
				function(data) {
					$scope.isDeletingGroup = false;

					if (data.success) {
						// Remove the group from the array.
						$scope.groups.splice($scope.groups.indexOf($scope.selectedGroup), 1);
						$scope.selectedGroup = null;
					} else {
						if (data.description)
							alert(data.description);
						else
							alert("Sorry, unable to delete.");
					}
				}
			);
		}
	}

	$scope.deleteUser = function() {
		if ($scope.selectedUser && !$scope.isDeletingUser) {
			$scope.isDeletingUser = true;
			entitlementService.deleteUser($scope.selectedUser).then(
				function(data) {
					$scope.isDeletingUser = false;

					if (data.success) {
						// Remove the user from the array.
						$scope.users.splice($scope.users.indexOf($scope.selectedUser), 1);
						$scope.selectedUser = null;
					} else {
						if (data.description)
							alert(data.description);
						else
							alert("Sorry, unable to delete.");
					}
				}
			);
		}
	}

	$scope.tabSelectHandler = function() {
		$scope.$broadcast("tabSelected");
	}

}]);

// Controller for the Edit Group Dialog.
var EditGroupDialogController = function ($scope, $modalInstance, entitlementService, group, folios, users) {
	// Data storage for the group name and description.
	$scope.form = {};

	$scope.form.name = group.name;
	$scope.form.description = group.description;

	$scope.init = function() {
		// Hack: Yes, ugly, shouldn't access the DOM from a controller but
		// Angular will display an empty option in the select if the options
		// are empty so manually managing the select control here and below.
		$scope.$groupFoliosSelected = $("#group-folios-multi-select");
		$scope.$groupUsersSelected = $("#group-users-multi-select");

		// Handler for when a user selects a folio to delete.
		$scope.$groupFoliosSelected.on("change", function(e) {
			var productId = $(e.currentTarget).val();
			if (productId)
				$scope.form.folioToDelete = _.find($scope.form.groupFolios, {productId: productId});
		});

		// Handler for when a user selects a user to delete.
		$scope.$groupUsersSelected.on("change", function(e) {
			var id = Number($(e.currentTarget).val());
			if (id)
				$scope.form.userToDelete = _.find($scope.form.groupUsers, {id: id});
		});
	}

	$scope.addFolio_clickHandler = function() {
		var folio = $scope.form.folioToAdd;

		// Add the folio to groupFolios.
		$scope.form.groupFolios.push(folio);

		// Add the html to the <select>.
		$scope.$groupFoliosSelected.append("<option value='" + folio.productId + "'>" + folio.label + "</option>");

		var removeIndex = $scope.form.availableFolios.indexOf(folio);
		$scope.form.availableFolios.splice(removeIndex, 1);

		// Set the default to the first one.
		$scope.form.folioToAdd = $scope.form.availableFolios[0];
	}

	$scope.addUser_clickHandler = function() {
		var user = $scope.form.userToAdd;

		// Add the folio to groupFolios.
		$scope.form.groupUsers.push(user);

		// Add the html to the <select>.
		$scope.$groupUsersSelected.append("<option value='" + user.id + "'>" + user.name + "</option>");

		var removeIndex = $scope.form.availableUsers.indexOf(user);
		$scope.form.availableUsers.splice(removeIndex, 1);

		// Set the default to the first one.
		$scope.form.userToAdd = $scope.form.availableUsers[0];
	}
	
	$scope.removeFolio_clickHandler = function() {
		if ($scope.form.folioToDelete) {
			$scope.form.availableFolios.push($scope.form.folioToDelete);

			var removeIndex = $scope.form.groupFolios.indexOf($scope.form.folioToDelete);
			$scope.form.groupFolios.splice(removeIndex, 1);

			// Remove the <option> from the <select>
			// ID won't work so need to use class.
			$(".group-folios-multi-select option[value='" + $scope.form.folioToDelete.productId + "']").remove();

			if ($scope.form.groupFolios.length > 0) {
				var selectedIndex = Math.max(0, removeIndex - 1);
				$scope.form.folioToDelete = $scope.form.groupFolios[selectedIndex];
				$scope.$groupFoliosSelected.val($scope.form.folioToDelete.productId)
			} else {
				$scope.form.folioToDelete = null;
			}
		}
	}
	
	$scope.removeUser_clickHandler = function() {
		var user = $scope.form.userToDelete;
		if (user) {
			$scope.form.availableUsers.push(user);

			var removeIndex = $scope.form.groupUsers.indexOf(user);
			$scope.form.groupUsers.splice(removeIndex, 1);

			// Remove the <option> from the <select>
			// ID won't work so need to use class.
			$(".group-users-multi-select option[value='" + user.id + "']").remove();

			if ($scope.form.groupUsers.length > 0) {
				var selectedIndex = Math.max(0, removeIndex - 1);
				$scope.form.userToDelete = $scope.form.groupUsers[selectedIndex];
				$scope.$groupUsersSelected.val($scope.form.userToDelete.id)
			} else {
				$scope.form.userToDelete = null;
			}
		}
	}

	$scope.ok_clickHandler = function () {
		if (!$scope.form.name) { // Make sure the fields are not empty.
			$scope.form.errorMessage = "Please enter a group name.";
		} else {
			$scope.form.isUploadingGroupEdit = true;

			var productIds = [];
			_.each($scope.form.groupFolios, function(element) {
				productIds.push(element.productId);
			})

			var userIds = [];
			_.each($scope.form.groupUsers, function(element) {
				userIds.push(element.id);
			})

			entitlementService.updateGroup(group.guid, group.id, $scope.form.name, $scope.form.description, productIds, userIds).then(
				function(data) {
					$scope.form.isUploadingGroupEdit = false;
					if (data.success) {
						group.name = $scope.form.name;
						group.description = $scope.form.description;
						$modalInstance.close($scope.form);
					} else {
						$scope.form.errorMessage = data.description || "Sorry, unable to update this group.";
					}
				},
				function() {
					$scope.form.isUploadingGroupEdit = false;
					$scope.form.errorMessage = "Sorry, unable to reach the database.";
				}
			);
		}
	};

	$scope.cancel_clickHandler = function () {
		$modalInstance.dismiss("cancel");
	};

	$scope.form.isGettingFoliosForGroup = true;
	$scope.foliosForGroupHash = {};

	entitlementService.getFoliosForGroup(group.guid, group.id).then(
		function(data) {
			$scope.form.isGettingFoliosForGroup = false;
			if (data.success) {
				// Get the associated folio objects from data.folios which is a list of productIds.
				var groupFolios = [];
				var optionTags = "";
				_.each(data.folios, function(element) { // Loop through the folios.
					var productId = element;
					var folio = _.find(folios, {productId: productId});
					groupFolios.push(folio); // Get the folio object for the productId.
					optionTags += "<option value='" + productId + "'>" + folio.label + "</option>"
				})
				$scope.form.groupFolios = groupFolios;

				$scope.$groupFoliosSelected.append(optionTags);

				// Store the folios associated with the group in a hash for look up.
				_.each(groupFolios, function(element) {
					$scope.foliosForGroupHash[element.productId] = element.productId;
				});

				// Make a copy of folios since it will change based on what the group already has assigned.
				var availableFolios = folios.slice(0);

				// Loop through the available folios and remove the folios already assigned to the group.
				var len = availableFolios.length;
				for (var i = len - 1; i >= 0; i--) {
					var productId = availableFolios[i].productId;
					if ($scope.foliosForGroupHash[productId])
						availableFolios.splice(i, 1);
				}

				$scope.form.availableFolios = availableFolios;

				//$scope.form.folioToAdd = availableFolios[0];
			} else {
				alert(data.description);
			}
		},
		function() {
			alert("Sorry, unable to reach the database.");
		}
	);

	$scope.form.isGettingUsersForGroup = true;
	$scope.usersForGroupHash = {};

	entitlementService.getUsersForGroup(group.guid, group.id).then(
		function(data) {
			$scope.form.isGettingUsersForGroup = false;
			if (data.success) {
				// Get the associated user objects from data.users which is a list of ids.
				var groupUsers = [];
				var optionTags = "";
				_.each(data.users, function(element) { // Loop through the users.
					var id = element;
					var user = _.find(users, {id: id}); // Get the group object for the id.
					groupUsers.push(user);
					optionTags += "<option value='" + id + "'>" + user.name + "</option>"
				})
				$scope.form.groupUsers = groupUsers;

				$scope.$groupUsersSelected.append(optionTags);

				// Store the users associated with the group in a hash for look up.
				_.each(groupUsers, function(element) {
					$scope.usersForGroupHash[element.id] = element.id;
				});

				// Make a copy of users since it will change based on what the group already has assigned.
				var availableUsers = users.slice(0);

				// Loop through the available users and remove the users already assigned to the group.
				var len = availableUsers.length;
				for (var i = len - 1; i >= 0; i--) {
					var id = availableUsers[i].id;
					if ($scope.usersForGroupHash[id])
						availableUsers.splice(i, 1);
				}

				$scope.form.availableUsers = availableUsers;

				//$scope.form.userToAdd = availableUsers[0];
			} else {
				alert(data.description);
			}
		},
		function() {
			alert("Sorry, unable to reach the database.");
		}
	);
};
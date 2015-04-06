// Controller for the Edit User dialog.
var EditUserDialogController = function ($scope, $modalInstance, $rootScope, $timeout, entitlementService, user, folios, groups) {
	// Placeholder for ie. Need to set at timeout otherwise the password fields can't be retrieved
	$scope.partialInitHandler = function() {
		setTimeout(function() {
			$("input").placeholder(); 
		}, 10);
	}

	// Data storage for the user name and description.
	$scope.form = {};

	$scope.form.name = user.name;
	$scope.form.description = user.description;

	$scope.init = function() {
		// Hack: Yes, ugly, shouldn't access the DOM from a controller but
		// Angular will display an empty option in the select if the options
		// are empty so manually managing the select control here and below.
		$scope.$userFoliosSelected = $("#user-folios-multi-select");
		$scope.$userGroupsSelected = $("#user-groups-multi-select");

		// Handler for when a user selects a folio to delete.
		$scope.$userFoliosSelected.on("change", function(e) {
			var productId = $(e.currentTarget).val();
			if (productId)
				$scope.form.folioToDelete = _.find($scope.form.userFolios, {productId: productId});
		});

		// Handler for when a user selects a group to delete.
		$scope.$userGroupsSelected.on("change", function(e) {
			var id = Number($(e.currentTarget).val());
			if (id)
				$scope.form.groupToDelete = _.find($scope.form.userGroups, {id: id});
		});
	}

	// Handler for when a list of folios is downloaded. Server is polled.
	var removeGetFoliosHandler = $rootScope.$on("getFolios", function(e, folios) {
		// First figure out the available folio list.
		var foliosForUserHash = {};
		var availableFolios = folios.slice(0);
		var userFolios = $scope.form.userFolios;
		// Store the folios associated with the user in a hash for look up.
		_.each(userFolios, function(element) {
			foliosForUserHash[element.productId] = element.productId;
		});

		var availableFoliosHash = {};
		// Loop through the available folios and remove the folios already assigned to the user.
		var len = availableFolios.length;
		for (var i = len - 1; i >= 0; i--) {
			var productId = availableFolios[i].productId;
			if (foliosForUserHash[productId])
				availableFolios.splice(i, 1);

			availableFoliosHash[productId] = productId;
		}
		$scope.form.availableFolios = availableFolios;

		// Next, loop through the user's folios and remove any that are no longer in the list of folios.
		var len = userFolios.length;
		for (var i = len - 1; i >= 0; i--) {
			var productId = userFolios[i].productId;
			if (!availableFoliosHash[productId]) { // folio no longer exists so set it as the one to delete
				$scope.form.folioToDelete = userFolios[i];
				$scope.removeFolio_clickHandler(false);
			}
		}
	});

	// Remove the handler when the scope is destroyed.
	$scope.$on("$destroy", function() {
		removeGetFoliosHandler();
	});

	$scope.addFolio_clickHandler = function() {
		var folio = $scope.form.folioToAdd;

		// Add the folio to userFolios.
		$scope.form.userFolios.push(folio);

		// Add the html to the <select>.
		$scope.$userFoliosSelected.append("<option value='" + folio.productId + "'>" + folio.label + "</option>");

		var removeIndex = $scope.form.availableFolios.indexOf(folio);
		$scope.form.availableFolios.splice(removeIndex, 1);

		// Set the default to the first one.
		$scope.form.folioToAdd = $scope.form.availableFolios[0];

		// IE HACK: need to force a redraw so the select items render correctly.
		$scope.$userFoliosSelected.css("width", 0).css("width", "").hide().show();
	}

	$scope.addGroup_clickHandler = function() {
		var group = $scope.form.groupToAdd;

		// Add the group to userGroups.
		$scope.form.userGroups.push(group);

		// Add the html to the <select>.
		$scope.$userGroupsSelected.append("<option value='" + group.id + "'>" + group.name + "</option>");

		var removeIndex = $scope.form.availableGroups.indexOf(group);
		$scope.form.availableGroups.splice(removeIndex, 1);

		// Set the default to the first one.
		$scope.form.groupToAdd = $scope.form.availableGroups[0];

		// IE HACK: need to force a redraw so the select items render correctly.
		$scope.$userGroupsSelected.css("width", 0).css("width", "").hide().show();
	}

	$scope.removeFolio_clickHandler = function(isUserRemoved) {
		if ($scope.form.folioToDelete) {
			// Add the folio back to the list of available folios.
			if (isUserRemoved) // This will not be the case when a folio is removed due to an unpublish.
				$scope.form.availableFolios.push($scope.form.folioToDelete);

			var removeIndex = $scope.form.userFolios.indexOf($scope.form.folioToDelete);
			$scope.form.userFolios.splice(removeIndex, 1);

			// Remove the <option> from the <select>
			// ID won't work so need to use class.
			$(".user-folios-multi-select option[value='" + $scope.form.folioToDelete.productId + "']").remove();

			if (isUserRemoved) {
				if ($scope.form.userFolios.length > 0) {
					var selectedIndex = Math.max(0, removeIndex - 1);
					$scope.form.folioToDelete = $scope.form.userFolios[selectedIndex];
					$scope.$userFoliosSelected.val($scope.form.folioToDelete.productId)
				} else {
					$scope.form.folioToDelete = null;
				}
			}
		}
	}

	$scope.removeGroup_clickHandler = function() {
		if ($scope.form.groupToDelete) {
			$scope.form.availableGroups.push($scope.form.groupToDelete);

			var removeIndex = $scope.form.userGroups.indexOf($scope.form.groupToDelete);
			$scope.form.userGroups.splice(removeIndex, 1);

			// Remove the <option> from the <select>
			// ID won't work so need to use class.
			$(".user-groups-multi-select option[value='" + $scope.form.groupToDelete.id + "']").remove();

			if ($scope.form.userGroups.length > 0) {
				var selectedIndex = Math.max(0, removeIndex - 1);
				$scope.form.groupToDelete = $scope.form.userGroups[selectedIndex];
				$scope.$userGroupsSelected.val($scope.form.groupToDelete.id)
			} else {
				$scope.form.groupToDelete = null;
			}
		}
	}

	$scope.ok_clickHandler = function () {
		if (!$scope.form.name) { // Make sure the fields are not empty.
			$scope.form.errorMessage = "Please enter a user name.";
		} else if ($scope.form.password1 != $scope.form.password2) {
			$scope.form.errorMessage = "Please verify that your passwords match.";
		} else {
			$scope.form.isUploadingUserEdit = true;

			var productIds = [];
			_.each($scope.form.userFolios, function(element) {
				productIds.push(element.productId);
			})

			var groupIds = [];
			_.each($scope.form.userGroups, function(element) {
				groupIds.push(element.id);
			})

			entitlementService.updateUser(user.guid, user.id, $scope.form.name, $scope.form.password1, $scope.form.description, productIds, groupIds).then(
				function(data) {
					$scope.form.isUploadingUserEdit = false;
					if (data.success) {
						user.name = $scope.form.name;
						user.description = $scope.form.description;
						user.password = $scope.form.password1;
						$modalInstance.close($scope.form);
					} else {
						$scope.form.errorMessage = data.description || "Sorry, unable to update this user.";
					}
				},
				function() {
					$scope.form.isUploadingUserEdit = false;
					$scope.form.errorMessage = "Sorry, unable to reach the database.";
				}
			);
		}
	};

	$scope.cancel_clickHandler = function () {
		$modalInstance.dismiss("cancel");
	};

	$scope.form.isGettingFoliosForUser = true;

	$scope.foliosForUserHash = {};

	entitlementService.getFoliosForUser(user.guid, user.id).then(
		function(data) {
			$scope.form.isGettingFoliosForUser = false;
			if (data.success) {
				// Get the associated folio objects from data.folios which is a list of productIds.
				var userFolios = [];
				var optionTags = "";
				_.each(data.folios, function(element) { // Loop through the folios.
					var productId = element;
					var folio = _.find(folios, {productId: productId});
					// Make sure there is an associated folio for this productId.
					// If the folio was deleted or made private then there won't be one.
					if (folio) {
						userFolios.push(folio); // Get the folio object for the productId.
						optionTags += "<option value='" + productId + "'>" + folio.label + "</option>"

						// Store the folios associated with the user in a hash for look up.
						$scope.foliosForUserHash[folio.productId] = folio.productId;
					}
				})
				$scope.form.userFolios = userFolios;

				$scope.$userFoliosSelected.append(optionTags);

				// Make a copy of folios since it will change based on what the user already has assigned.
				var availableFolios = folios.slice(0);

				// Loop through the available folios and remove the folios already assigned to the user.
				var len = availableFolios.length;
				for (var i = len - 1; i >= 0; i--) {
					var productId = availableFolios[i].productId;
					if ($scope.foliosForUserHash[productId])
						availableFolios.splice(i, 1);
				}

				$scope.form.availableFolios = availableFolios;

				// IE HACK: need to force a redraw so the select items render correctly.
				$scope.$userFoliosSelected.css("width", 0).css("width", "").hide().show();
			} else {
				alert(data.description);
			}
		},
		function() {
			alert("Sorry, unable to reach the database.");
		}
	);

	$scope.form.isGettingGroupsForUser = true;

	$scope.groupsForUserHash = {};

	entitlementService.getGroupsForUser(user.guid, user.id).then(
		function(data) {
			$scope.form.isGettingGroupsForUser = false;
			if (data.success) {
				// Get the associated group objects from data.group which is a list of productIds.
				var userGroups = [];
				var optionTags = ""; // The options tags for the user's groups.
				_.each(data.groups, function(element) { // Loop through the groups.
					var id = element;
					var group = _.find(groups, {id: id});
					userGroups.push(group); // Get the group object for the productId.
					optionTags += "<option value='" + id + "'>" + group.name + "</option>"
				})
				$scope.form.userGroups = userGroups;

				$scope.$userGroupsSelected.append(optionTags);

				// Store the groups associated with the user in a hash for look up.
				_.each(userGroups, function(element) {
					$scope.groupsForUserHash[element.id] = element.id;
				});

				// Make a copy of groups since it will change based on what the user already has assigned.
				var availableGroups = groups.slice(0);

				// Loop through the available groups and remove the groups already assigned to the user.
				var len = availableGroups.length;
				for (var i = len - 1; i >= 0; i--) {
					var id = availableGroups[i].id;
					if ($scope.groupsForUserHash[id])
						availableGroups.splice(i, 1);
				}

				$scope.form.availableGroups = availableGroups;

				// IE HACK: need to force a redraw so the select items render correctly.
				$scope.$userGroupsSelected.css("width", 0).css("width", "").hide().show();
			} else {
				alert(data.description);
			}
		},
		function() {
			alert("Sorry, unable to reach the database.");
		}
	);
};

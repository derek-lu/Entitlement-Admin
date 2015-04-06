// Controller for the Edit Folio Dialog.
var EditFolioDialogController = function ($scope, $modalInstance, entitlementService, folio, groups, users, guid) {
	// Placeholder for ie. 
	$scope.partialInitHandler = function() {
		$("input").placeholder(); 
	}
	
	// Data storage for the group name and description.
	$scope.folio = {};
	$scope.form = {};
	$scope.folio = folio;
	$scope.groups = groups;
	$scope.guid = guid;

	$scope.init = function() {
		// Hack: Yes, ugly, shouldn't access the DOM from a controller but
		// Angular will display an empty option in the select if the options
		// are empty so manually managing the select control here and below.
		$scope.$folioGroupsSelected = $("#folio-groups-multi-select");
		$scope.$folioUsersSelected = $("#folio-users-multi-select");

		// Handler for when a user selects a group to delete.
		$scope.$folioGroupsSelected.on("change", function(e) {
			var id = $(e.currentTarget).val();
			if (id) {
				$scope.form.groupToDelete = _.find($scope.form.folioGroups, {id: Number(id)});
			}
		});

		// Handler for when a user selects a user to delete.
		$scope.$folioUsersSelected.on("change", function(e) {
			var id = Number($(e.currentTarget).val());
			if (id) {
				$scope.form.userToDelete = _.find($scope.form.folioUsers, {id: Number(id)});
			}
		});
	}

	$scope.addGroup_clickHandler = function() {
		// Add the group to folioGroups.
		$scope.form.folioGroups.push($scope.form.groupToAdd);

		// Add the html to the <select>.
		$scope.$folioGroupsSelected.append("<option value='" + $scope.form.groupToAdd.id + "'>" + $scope.form.groupToAdd.name + "</option>");

		var removeIndex = $scope.form.availableGroups.indexOf($scope.form.groupToAdd);
		$scope.form.availableGroups.splice(removeIndex, 1);

		// Set the default to the first one.
		$scope.form.groupToAdd = $scope.form.availableGroups[0];

		// IE HACK: need to force a redraw so the select items render correctly.
		$scope.$folioGroupsSelected.css("width", 0).css("width", "").hide().show();
	}

	$scope.addUser_clickHandler = function() {
		var user = $scope.form.userToAdd;
		// Add the user to folioUsers.
		$scope.form.folioUsers.push(user);

		// Add the html to the <select>.
		$scope.$folioUsersSelected.append("<option value='" + user.id + "'>" + user.name + "</option>");

		var removeIndex = $scope.form.availableUsers.indexOf(user);
		$scope.form.availableUsers.splice(removeIndex, 1);

		// Set the default to the first one.
		$scope.form.userToAdd = $scope.form.availableUsers[0];

		// IE HACK: need to force a redraw so the select items render correctly.
		$scope.$folioUsersSelected.css("width", 0).css("width", "").hide().show();
	}

	$scope.removeFolio_clickHandler = function() {
		if ($scope.form.groupToDelete) {
			$scope.form.availableGroups.push($scope.form.groupToDelete);

			var removeIndex = $scope.form.folioGroups.indexOf($scope.form.groupToDelete);
			$scope.form.folioGroups.splice(removeIndex, 1);

			// Remove the <option> from the <select>
			$(".folio-groups-multi-select option[value='" + $scope.form.groupToDelete.id + "']").remove();

			if ($scope.form.folioGroups.length > 0) {
				var selectedIndex = Math.max(0, removeIndex - 1);
				$scope.form.groupToDelete = $scope.form.folioGroups[selectedIndex];
				$scope.$folioGroupsSelected.val($scope.form.groupToDelete.id);

				if (!$scope.form.groupToAdd)
					$scope.form.groupToAdd = $scope.form.availableGroups[0];
			} else {
				$scope.form.groupToDelete = null;
			}
		}
	}

	$scope.removeUser_clickHandler = function() {
		var user = $scope.form.userToDelete;
		if (user) {
			$scope.form.availableUsers.push(user);

			var removeIndex = $scope.form.folioUsers.indexOf(user);
			$scope.form.folioUsers.splice(removeIndex, 1);

			// Remove the <option> from the <select>
			$(".folio-users-multi-select option[value='" + user.id + "']").remove();

			if ($scope.form.folioUsers.length > 0) {
				var selectedIndex = Math.max(0, removeIndex - 1);
				$scope.form.userToDelete = $scope.form.folioUsers[selectedIndex];
				$scope.$folioUsersSelected.val($scope.form.userToDelete.id);

				if (!$scope.form.userToAdd)
					$scope.form.userToAdd = $scope.form.availableUsers[0];
			} else {
				$scope.form.userToDelete = null;
			}
		}
	}

	$scope.ok_clickHandler = function () {
		$scope.form.isUploadingFolioEdit = true;

		// Put the selected groupIds in an array.
		var groupIds = [];
		_.each($scope.form.folioGroups, function(element) {
			groupIds.push(element.id);
		})

		var userIds = [];
		_.each($scope.form.folioUsers, function(element) {
			userIds.push(element.id);
		})

		entitlementService.updateFolio(folio.productId, guid, groupIds, userIds).then(
			function(data) {
				$scope.form.isUploadingFolioEdit = false;
				if (data.success)
					$modalInstance.close($scope.form);
				else
					$scope.form.errorMessage = data.description || "Sorry, unable to update this folio.";
			},
			function() {
				$scope.form.isUploadingFolioEdit = false;
				$scope.form.errorMessage = "Sorry, unable to reach the database.";
			}
		);
	};

	$scope.cancel_clickHandler = function () {
		$modalInstance.dismiss("cancel");
	};

	$scope.form.isGettingGroupsForFolio = true;

	$scope.groupsForFolioHash = {};

	entitlementService.getGroupsForFolio(folio.productId, $scope.guid).then(
		function(data) {
			$scope.form.isGettingGroupsForFolio = false;
			if (data.success) {
				// Loop through data.groups which is a list of groups ids and map them to the group objects.
				var folioGroups = [];
				var optionTags = "";
				_.each(data.groups, function(element) { // Loop through the returned groups.
					var group = _.find(groups, {id: element}); // Get the group object for the group id.
					folioGroups.push(group);
					optionTags += "<option value='" + group.id + "'>" + group.name + "</option>"
				});

				folioGroups.sort(function(a, b) {
					return a.name > b.name;
				})

				$scope.form.folioGroups = folioGroups;

				$scope.$folioGroupsSelected.append(optionTags);

				// Store the groups associated with the folio in a hash for look up.
				_.each(folioGroups, function(element) {
					$scope.groupsForFolioHash[element.id] = element.id;
				});

				// Make a copy of groups since it will change based on what the group already has assigned.
				var availableGroups = groups.slice(0);

				// Loop through the available groups and remove the groups already assigned to the folios.
				var len = availableGroups.length;
				for (var i = len - 1; i >= 0; i--) {
					var id = availableGroups[i].id;
					if ($scope.groupsForFolioHash[id])
						availableGroups.splice(i, 1);
				}

				$scope.form.availableGroups = availableGroups;

				// IE HACK: need to force a redraw so the select items render correctly.
				$scope.$folioGroupsSelected.css("width", 0).css("width", "").hide().show();
			} else {
				alert(data.description);
			}
		},
		function() {
			alert("Sorry, unable to reach the database.");
		}
	);


	$scope.usersForFolioHash = {};

	entitlementService.getUsersForFolio(folio.productId, $scope.guid).then(
		function(data) {
			if (data.success) {
				// Loop through data.users which is a list of users ids and map them to the group objects.
				var folioUsers = [];
				var optionTags = "";
				_.each(data.users, function(element) { // Loop through the returned users.
					var user = _.find(users, {id: element}); // Get the group object for the group id.
					folioUsers.push(user);
					optionTags += "<option value='" + user.id + "'>" + user.name + "</option>"
				});

				folioUsers.sort(function(a, b) {
					return a.name > b.name;
				})

				$scope.form.folioUsers = folioUsers;

				$scope.$folioUsersSelected.append(optionTags);

				// Store the user associated with the folio in a hash for look up.
				_.each(folioUsers, function(element) {
					$scope.usersForFolioHash[element.id] = element.id;
				});

				// Make a copy of users since it will change based on what the user already has assigned.
				var availableUsers = users.slice(0);

				// Loop through the available users and remove the users already assigned to the folio.
				var len = availableUsers.length;
				for (var i = len - 1; i >= 0; i--) {
					var id = availableUsers[i].id;
					if ($scope.usersForFolioHash[id])
						availableUsers.splice(i, 1);
				}

				$scope.form.availableUsers = availableUsers;

				// IE HACK: need to force a redraw so the select items render correctly.
				$scope.$folioUsersSelected.css("width", 0).css("width", "").hide().show();
			} else {
				alert(data.description);
			}
		},
		function() {
			alert("Sorry, unable to reach the database.");
		}
	);
};

// Handles user login.
app.service("loginService", ["$http", "$rootScope", function($http, $rootScope) {
	return {
		// If a guid exists then the user is a valid DPS user.
		login: function(adobeId, password) {
			var xsrf = $.param({adobeId: adobeId, password: password});
			return $http({
						method: "POST",
						url:"php/getGUID.php",
						data: xsrf,
						headers: {"Content-Type": "application/x-www-form-urlencoded"}
					}).then(function(result) {
						$rootScope.csrfToken = result.data.csrfToken;
						return result.data;
				}
			);
		}
	};
}]);

app.service("entitlementService", ["$http", "$rootScope", function($http, $rootScope) {
	return {
		getFolios: function(accountId) {
			return $http({
					method: "POST",
					url:"php/getFolios.php",
					params: {accountId: accountId}
				}).then(function(result) {
					var xml = new window.DOMParser().parseFromString(result.data, "text/xml");

					var issueNodes = xml.getElementsByTagName("issue");
					var len = issueNodes.length;

					// Store the folios in a hash so the same folio isn't added more than once.
					// This will occur for renditions.
					var folioHash = {};
					if (len > 0) {
						var folios = [];
						for (var i = 0; i < len; i++) {
							var issueNode = issueNodes[i];

							var attributes = issueNode.attributes;
							var productId = attributes.getNamedItem("productId").value;
							if (!folioHash[productId]) { // Make sure this folio has not already been added.
								folioHash[productId] = true;

								// Get the attributes
								var folio = {};
								folio.productId = productId;

								// Loop through the nodes.
								var childNodes = issueNode.childNodes;
								var numNodes = childNodes.length;
								for (var j = 0; j < numNodes; j++) {
									var childNode = childNodes[j];
									if (childNode.nodeType == 1) {
										var nodeName = childNode.nodeName;
										if (childNode.nodeName == "publicationDate") {
											// 2011-06-22T07:00:00Z.
											var pubDate = childNode.firstChild.nodeValue.split("-");
											var date = new Date(pubDate[0], Number(pubDate[1]) - 1, pubDate[2].substr(0, 2));
											folio["publicationDate"] = date;
										} else if (childNode.firstChild) {
											folio[childNode.nodeName] = childNode.firstChild.nodeValue;
										}
									}
								}

								// Create a label field for each folio.
								folio.label = folio.magazineTitle + " - " + folio.issueNumber;

								folios.push(folio);
							}
						}

						// Sort ascending by label.
						folios = folios.sort(function(a, b) {
							if(a.label < b.label)
								return -1;
							if(a.label > b.label)
								return 1;

							return 0;
						});

						return folios;
					}
				}
			);
		},

		setAppId: function(guid, appId) {
			var xsrf = $.param({guid: guid, appId: appId, csrfToken: $rootScope.csrfToken});
			return $http({
						method: "POST",
						url: "php/setAppId.php",
						data: xsrf,
						headers: {"Content-Type": "application/x-www-form-urlencoded"}
					}).then(function(result) {
						return result.data;
				}
			);
		},

		getAppId: function(guid) {
			var xsrf = $.param({guid: guid});
			return $http({
					method: "POST",
					url: "php/getAppId.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
						return result.data;
				}
			);
		},

		addUser: function(guid, name, password, description) {
			var user = {
				guid: guid, name: name, password: password, description: description, csrfToken: $rootScope.csrfToken
			};
			var xsrf = $.param(user);
			return $http({
					method: "POST",
					url: "php/addUser.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		getFoliosForUser: function(guid, id) {
			var xsrf = $.param({guid: guid, id: id});
			return $http({
					method: "POST",
					url:"php/getFoliosForUser.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		getGroupsForUser: function(guid, id) {
			var xsrf = $.param({guid: guid, id: id});
			return $http({
					method: "POST",
					url:"php/getGroupsForUser.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		deleteUser: function(user) {
			var xsrf = $.param({id: user.id, guid: user.guid, csrfToken: $rootScope.csrfToken});
			return $http({
					method: "POST",
					url: "php/deleteUser.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		getUsers: function(guid) {
			var xsrf = $.param({guid: guid, csrfToken: $rootScope.csrfToken});
			return $http({
					method: "POST",
					url: "php/getUsers.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		addGroup: function(guid, name, description) {
			var group = {
				guid: guid, name: name, description: description, csrfToken: $rootScope.csrfToken
			};
			var xsrf = $.param(group);
			return $http({
					method: "POST",
					url: "php/addGroup.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		deleteGroup: function(group) {
			var xsrf = $.param({id: group.id, guid: group.guid, csrfToken: $rootScope.csrfToken});
			return $http({
					method: "POST",
					url: "php/deleteGroup.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		getGroups: function(guid) {
			var xsrf = $.param({guid: guid, csrfToken: $rootScope.csrfToken});
			return $http({
					method: "POST",
					url: "php/getGroups.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		getFoliosForGroup: function(guid, id) {
			var xsrf = $.param({guid: guid, id: id});
			return $http({
					method: "POST",
					url:"php/getFoliosForGroup.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		getUsersForGroup: function(guid, id) {
			var xsrf = $.param({guid: guid, id: id});
			return $http({
					method: "POST",
					url:"php/getUsersForGroup.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		updateGroup: function(guid, id, name, description, folios, users) {
			var group = {
				guid: guid,
				id: id,
				name: name,
				description: description,
				folios: folios,
				users: users,
				csrfToken: $rootScope.csrfToken
			};

			var xsrf = $.param({group: JSON.stringify(group)});
			return $http({
					method: "POST",
					url:"php/updateGroup.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		updateUser: function(guid, id, name, password, description, folios, groups) {
			var user = {
				guid: guid,
				id: id,
				name: name,
				password: password,
				description: description,
				folios: folios,
				groups: groups,
				csrfToken: $rootScope.csrfToken
			};

			var xsrf = $.param({user: JSON.stringify(user)});
			return $http({
					method: "POST",
					url:"php/updateUser.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		getGroupsForFolio: function(productId, guid) {
			var xsrf = $.param({productId: productId, guid: guid});
			return $http({
					method: "POST",
					url:"php/getGroupsForFolio.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		getUsersForFolio: function(productId, guid) {
			var xsrf = $.param({productId: productId, guid: guid});
			return $http({
					method: "POST",
					url:"php/getUsersForFolio.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		},

		updateFolio: function(productId, guid, groupIds, userIds) {
			var data = {
				productId: productId,
				guid: guid,
				groupIds: groupIds,
				userIds: userIds,
				csrfToken: $rootScope.csrfToken
			};

			var xsrf = $.param({data: JSON.stringify(data)});
			return $http({
					method: "POST",
					url:"php/updateFolio.php",
					data: xsrf,
					headers: {"Content-Type": "application/x-www-form-urlencoded"}
				}).then(function(result) {
					return result.data;
				}
			);
		}
	};
}]);



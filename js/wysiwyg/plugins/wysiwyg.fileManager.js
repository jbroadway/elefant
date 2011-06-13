/**
 * File Manager plugin for jWYSIWYG
 * 
 * Yotam Bar-On, 2011
 * 
 * The file manager ui uses the Silk icon set from FAMFAMFAM
 * 
 */

(function ($) {
	"use strict";

	if (undefined === $.wysiwyg) {
		throw "wysiwyg.fileManager.js depends on $.wysiwyg";
	}

	/*
	 * Wysiwyg namespace: public properties and methods
	 */
	// Only for show
	var fileManager = {
		name: "fileManager",
		version: "0.98", // Same as jwysiwyg
		ajaxHandler: "",
		selected: null,
		setAjaxHandler: function (_handler) {
			this.ajaxHandler = _handler;
			this.ready = true;
			
			return this;
		},
		ready: false,
		init: function (callback) {
			if (this.ready) {
				var manager = new fileManagerObj(this.ajaxHandler);
				manager.load(callback);
			} else {
				console.log("$.wysiwyg.fileManager: Must set ajax handler first, using $.wysiwyg.fileManager.setAjaxHandler()");
				return false;
			}
		}
	};

	// Register:
	$.wysiwyg.plugin.register(fileManager);
	
	// Private object:
	function fileManagerObj (_handler) {
		this.handler = _handler;
		this.loaded = false;
		this.move = false;
		this.rename = false;
		this.remove = false;
		this.upload = false;
		this.mkdir = false;
		this.baseUrl = "";
		this.selectedFile = "";
		this.curDir = "/";
		this.curListHtml = "";
		
		/*
		 * Methods
		 */
		var console = $.wysiwyg.console;
		console.log(this.handler);
		 
		this.load = function (callback) {
			var self = this;
			self.loaded = true;
			self.authenticate(function (response) {
				if (response !== "success") {
					alert(response);
					return false;
				}
				// Wrap the file list:
				self.loadDir("/", function (fileList) {
					var uiHtml = 	'<div class="wysiwyg-files-wrapper">' +
									'<input type="text" name="url" />' +
									'<div id="wysiwyg-files-list-wrapper">'+fileList+'</div>';
					
					// If handler does not support upload, icon will not appear:
					if (self.upload.enabled) {
						uiHtml += 	'<div class="wysiwyg-files-action-upload" title="{{upload_action}}"></div>';
					}
					
					// If handler does not support mkdir, icon will not appear:
					if (self.mkdir.enabled) {
						uiHtml += 	'<div class="wysiwyg-files-action-mkdir" title="{{mkdir_action}}"></div>';
					}
					
					uiHtml += 	'<input style="display:none;" type="button" name="submit" value="{{select}}" />' +
								'</div>';
								
					uiHtml = self.i18n(uiHtml);
					if ($.wysiwyg.dialog) { 
						// Support for native $.wysiwyg.dialog()
						var _title = self.i18n("{{file_manager}}");
						var fileManagerUI = new $.wysiwyg.dialog(_handler, {
							"title": _title,
							"content": uiHtml,
							"close": function () {
								var dialog = $(".wysiwyg-dialog-content").find(".wysiwyg-files-wrapper");

								// Unbind live-events:
								// Actions:
								$(".wysiwyg-files-action-rename").die("click");
								$(".wysiwyg-files-action-remove").die("click");

								// li Elements:
								dialog.find("li").die("mouseenter");
								dialog.find("li").die("mouseleave");
								dialog.find("li").find("a").die("click");

								// Image previews:
								var _images = dialog.find("li.wysiwyg-files-png, li.wysiwyg-files-jpg, li.wysiwyg-files-jpeg, li.wysiwyg-files-gif, li.wysiwyg-files-ico, li.wysiwyg-files-bmp");
								_images.die("mouseenter");
								_images.die("mouseleave");
								_images.die("mouseover");

								// File selection:
								dialog.find("input[name=submit]").die("click");
								dialog.find("li.wysiwyg-files-file").die("dblclick");

							},
							"open": function (e, _dialog) {
								var dialog = $(".wysiwyg-dialog-content").find(".wysiwyg-files-wrapper");
								
								// Hover effect:
								dialog.find("li").live("mouseenter", function () {
									$(this).addClass("wysiwyg-files-hover");
									
									if ($(this).hasClass("wysiwyg-files-dir")) {
										$(this).addClass("wysiwyg-files-dir-expanded");
									}
									// Add action buttons:
									if (!$(this).hasClass("wysiwyg-files-dir-prev")) {
										$(".wysiwyg-files-action").remove();
										// If handler does not support remove, icon will not appear:
										if (self.remove.enabled) {
											var rmText = self.i18n("{{remove_action}}");
											$("<div/>", { "class": "wysiwyg-files-action wysiwyg-files-action-remove", "title": rmText }).appendTo(this);
										}
										
										// If handler does not support rename, icon will not appear:
										if (self.rename.enabled) {
											var rnText = self.i18n("{{rename_action}}");
											$("<div/>", { "class": "wysiwyg-files-action wysiwyg-files-action-rename", "title": rnText }).appendTo(this);
										}
										
									}
								}).live("mouseleave", function () {
									$(this).removeClass("wysiwyg-files-dir-expanded");
									$(this).removeClass("wysiwyg-files-hover");
									
									// Remove action buttons:
									$(".wysiwyg-files-action").remove();
								});
								
								// Browse:
								dialog.find("li").find("a").live("click", function (e) {
									self.selectedFile = $(this).attr("rel");
									$(".wysiwyg-files-wrapper").find("li").css("backgroundColor", "#FFF");
									
									// Browse Directory:
									if ($(this).parent("li").hasClass("wysiwyg-files-dir")) {
										self.selectedFile = "";
										dialog.find("input[name=submit]").hide();
										$(".wysiwyg-files-wrapper").find("input[name=url]").val('');
										$('#wysiwyg-files-list-wrapper').addClass("wysiwyg-files-ajax");
										$('#wysiwyg-files-list-wrapper').html("");
										self.loadDir($(this).attr("rel"), function (newFileList) {
											$('#wysiwyg-files-list-wrapper').html(newFileList);
											$('#wysiwyg-files-list-wrapper').removeClass("wysiwyg-files-ajax");
										});
										dialog.find("input[name=submit]").hide();
										
									// Select Entry:
									} else {
										self.selectedFile = $(this).text();
										$(this).parent("li").css("backgroundColor", "#BDF");
										$(".wysiwyg-files-wrapper").find("input[name=url]").val($(this).attr("rel"));
										dialog.find("input[name=submit]").show();
									}
									
								});

								// Select file bindings
								dialog.find("input[name=submit]").live("click", function () {
									var file = self.baseUrl + dialog.find("input[name=url]").val();
									fileManagerUI.close();
									self.loaded = false;
									callback(file);
								});
								
								// Image preview bindings
								dialog.find("li.wysiwyg-files-png, li.wysiwyg-files-jpg, li.wysiwyg-files-jpeg, li.wysiwyg-files-gif, li.wysiwyg-files-ico, li.wysiwyg-files-bmp").live("mouseenter", function () {
									var $this = $(this);
									$("<img/>", { "class": "wysiwyg-files-ajax wysiwyg-files-file-preview", "src": self.baseUrl + $this.find("a").attr("rel"), "alt": $this.text() }).appendTo("body");
									$("img.wysiwyg-files-file-preview").load(function () {
										$(this).removeClass("wysiwyg-files-ajax");
									});
								}).live("mousemove", function (e) {
									$("img.wysiwyg-files-file-preview").css("left", e.pageX + 15);
									$("img.wysiwyg-files-file-preview").css("top", e.pageY);
								}).live("mouseleave", function () {
									$("img.wysiwyg-files-file-preview").remove();
								});
								
								/* 
								 * Bind action buttons:
								 */

								// Remove:
								$(".wysiwyg-files-action-remove").live("click", function (e) {
									e.preventDefault();
									var entry = $(this).parent("li");
									// What are we deleting?
									var type = entry.hasClass("wysiwyg-files-file") ? "file" : "dir";
									var uiHtml = 	"<p>{{delete_message}}</p>" + 
													'<div class="">' + 
													'<input type="button" name="cancel" value="{{no}}" />' +
													'<input type="button" name="remove" value="{{yes}}" />' +
													"</div>";
									uiHtml = self.i18n(uiHtml);
									
									var _removeTitle = self.i18n("{{remove_title}}");
									
									var removeDialog = 	new $.wysiwyg.dialog(null, {
										"title": _removeTitle,
										"content": uiHtml,
										"close": function () {
											
										},
										"open": function (e, _dialog) {
											_dialog.find("input[name=remove]").bind("click", function () {
												var file = (type === "file") ? entry.find("a").text() : entry.find("a").attr("rel");
												self.removeFile(type, file, function (response) {
													self.loadDir(self.curDir, function (list) {
														$("#wysiwyg-files-list-wrapper").html(list);
													});
													removeDialog.close();
												});
											});
											
											_dialog.find("input[name=cancel]").bind("click", function () {
												removeDialog.close();
											});
										}
									});
									
									removeDialog.open();
								});

								// Rename
								$(".wysiwyg-files-action-rename").live("click", function (e) {
									e.preventDefault();
									var entry = $(this).parent("li");
									// What are we deleting?
									var type = entry.hasClass("wysiwyg-files-file") ? "file" : "dir";
									var uiHtml = 	'<div>' +
													'<input type="text" class="wysiwyg-files-textfield" name="newName" value="' + entry.find("a").text() + '" />' +
													'<input type="button" name="cancel" value="{{cancel}}" />' +
													'<input type="button" name="rename" value="{{rename}}" />' +
													'</div>';
									uiHtml = self.i18n(uiHtml);
									var _renameTitle = self.i18n("{{rename_title}}");
									
									var renameDialog = new $.wysiwyg.dialog(null, {
										"title": _renameTitle,
										"content": uiHtml,
										"close": function () {
											
										},
										"open": function (e, _dialog) {
											_dialog.find("input[name=rename]").bind("click", function () {
												var file = (type === "file") ? entry.find("a").text() : entry.find("a").attr("rel");
												self.renameFile(type, file, _dialog.find("input[name=newName]").val(), function (response) {
													self.loadDir(self.curDir, function (list) {
														$("#wysiwyg-files-list-wrapper").html(list);
													});
													renameDialog.close();
												});
											});
											
											_dialog.find("input[name=cancel]").bind("click", function () {
												renameDialog.close();
											});
										}
									});
									
									renameDialog.open();
									
								});

								// Create Directory
								$(".wysiwyg-files-action-mkdir").bind("click", function (e) {
									e.preventDefault();
									var uiHtml =	'<div>' +
													'<input type="text" class="wysiwyg-files-textfield" name="newName" value="{{new_directory}}" />' +
													'<input type="button" name="cancel" value="{{cancel}}" />' +
													'<input type="button" name="create" value="{{create}}" />' +
													'</div>';
									uiHtml = self.i18n(uiHtml);
									var _mkdirTitle = self.i18n("{{mkdir_title}}");
									var mkdirDialog = new $.wysiwyg.dialog(null, {
										"title": _mkdirTitle,
										"content": uiHtml,
										"close": function () {
											
										},
										"open": function (e, _dialog) {
											
											_dialog.find("input[name=create]").bind("click", function () {
												self.mkDir(_dialog.find("input[name=newName]").val(), function (response) {
													self.loadDir(self.curDir, function (list) {
														$("#wysiwyg-files-list-wrapper").html(list);
													});
													mkdirDialog.close();
												});
											});

											_dialog.find("input[name=cancel]").bind("click", function () {
												mkdirDialog.close();
											});
										}
									});
									mkdirDialog.open();								
								});

								// Upload File
								$(".wysiwyg-files-action-upload").bind("click", function (e) {
									self.loadUploadUI();
								});

							}
						});
						fileManagerUI.open();
					} else {
						// If neither .dialog() works..
						throw "$.wysiwyg.fileManager: Can't find a working '.dialog()' lib.";
					}
				});
			});
		};

		this.authenticate = function (callback) {
			if (!this.loaded) {
				return false;
			}
			var self = this;
			$.getJSON(self.handler, { "action": "auth", "auth": "jwysiwyg" }, function (json) {
				if (json.success) {
					self.move = json.data.move;
					self.rename = json.data.rename;
					self.remove = json.data.remove;
					self.mkdir = json.data.mkdir;
					self.upload = json.data.upload;
					self.baseUrl = json.data.baseUrl;
					callback("success");
				} else {
					console.log("$.wysiwyg.fileManager: Unable to authenticate handler.");
					callback(json.error);
				}
			});
		};

		this.loadDir = function (dir, callback) {
			if (!this.loaded) {
				return false;
			}
			var self = this;
			self.curDir = dir.replace(/\/$/, '') + '/';
			// Retreives list of files inside a certain directory:
			$.getJSON(self.handler, { "dir": self.curDir, "action": "list" }, function (json) {
				if (json.success) {
					callback(self.listDir(json));
				} else {
					alert(json.error);
				}
			});
		};

		/**
		 * Ajax Methods.
		 */

		// List Directory
		this.listDir = function (json) {
			if (!this.loaded) {
				return false;
			}
			var self = this;
			var treeHtml = '<ul class="wysiwyg-files-list">';
			if (self.curDir !== "/") {
				var prevDir = self.curDir.replace(/[^\/]+\/?$/, '');
				treeHtml += '<li class="wysiwyg-files-dir wysiwyg-files-dir-prev">' +
							'<a href="#" rel="'+prevDir+'" title="{{previous_directory}}">' +
							self.curDir +
							'</a></li>';
			}
			$.each(json.data.directories, function(name, dirPath) {
				treeHtml += '<li class="wysiwyg-files-dir">' +
							'<a href="#" rel="' + dirPath + '">' +
							name +
							'</a></li>';
			});			
			$.each(json.data.files, function(name, url) {
				var ext = name.replace(/^.*?\./, '').toLowerCase();
				treeHtml += '<li class="wysiwyg-files-file wysiwyg-files-'+ext+'">' +
							'<a href="#" rel="'+url+'">' +
							name +
							'</a></li>';
			});			
			treeHtml += '</ul>';
			
			return self.i18n(treeHtml);
		};
		
/**
 * Should be remembered for future implementation:
 * If handler does not support certain actions - do not show their icons/button.
 * Only action a handler MUST support is "list" (list directory).
 * 
 * Implemented: 28-May-2011, Yotam Bar-On
 */
		
		// Remove File Method:
		this.removeFile = function (type, file, callback) {
			if (!this.loaded) { return false; }
			if (!this.remove.enabled) { console.log("$.wysiwyg.fileManager: handler: remove is disabled."); return false; }
			
			var self = this;
			$.getJSON(self.remove.handler, { "action": "remove", "type": type, "dir": self.curDir, "file": file  }, function (json) {
				if (json.success) {
					alert(json.data);
				} else {
					alert(json.error);
				}
				callback(json);
			});
		};
		
		// Rename File Method
		this.renameFile = function (type, file, newName, callback) {
			if (!this.loaded) { return false; }
			if (!this.rename.enabled) { console.log("$.wysiwyg.fileManager: handler: rename is disabled."); return false; }
			
			var self = this;
			$.getJSON(self.rename.handler, { "action": "rename", "type": type, "dir": self.curDir, "file": file, "newName": newName  }, function (json) {
				if (json.success) {
					alert(json.data);
				} else {
					alert(json.error);
				}
				callback(json);
			});		
		};	

		// Make Directory Method
		this.mkDir = function (newName, callback) {
			if (!this.loaded) { return false; }
			if (!this.mkdir.enabled) { console.log("$.wysiwyg.fileManager: handler: mkdir is disabled."); return false; }
			
			var self = this;
			$.getJSON(self.mkdir.handler, { "action": "mkdir", "dir": self.curDir, "newName": newName  }, function (json) {
				if (json.success) {
					alert(json.data);
				} else {
					alert(json.error);
				}
				callback(json);
			});		
		};

		/**
		 * Currently we will not support moving of files. This will be supported only when a more interactive interface will be introduced.
		 */
		this.moveFile = function () {
			if (!this.loaded) { return false; }
			if (!this.move.enabled) { console.log("$.wysiwyg.fileManager: handler: move is disabled."); return false; }	
			var self = this;
			return false;
		};

		// Upload:
		this.loadUploadUI = function () {
			if (!this.loaded) { return false; }
			if (!this.upload.enabled) { console.log("$.wysiwyg.fileManager: handler: move is disabled."); return false; }	
			var self = this;
			var uiHtml = 	'<form enctype="multipart/form-data" method="post" action="' + self.upload.handler + '">' + 
							'<p><input type="file" name="handle" /><br>' + 
							'<input type="text" name="newName" style="width:250px; border:solid 1px !important;" /><br>' + 
							'<input type="text" name="action" style="display:none;" value="upload" /><br></p>' + 
							'<input type="text" name="dir" style="display:none;" value="' + self.curDir + '" /></p>' + 
							'<input type="submit" name="submit" value="{{submit}}" />' +
							'</form>';
			uiHtml = self.i18n(uiHtml);
								
			var _uploadTitle = self.i18n("{{upload_title}}");
			
			var dialog = new $.wysiwyg.dialog(null, {
				"title": _uploadTitle,
				"content": "",
				"open": function (e, _dialog) {

					$("<iframe/>", { "class": "wysiwyg-files-upload" }).load(function () {
						var $doc = $(this).contents();
						$doc.find("body").append(uiHtml);
						$doc.find("input[type=file]").change(function () {
							var $val = $(this).val();
							$val = $val.replace(/.*[\\\/]/, '');
							// Should implement validation of extensions before submitting form
							$doc.find("input[name=newName]").val($val);
						});

					}).appendTo(_dialog.find(".wysiwyg-dialog-content"));

				},
				"close": function () {
					self.loadDir(self.curDir, function (list) {
						$("#wysiwyg-files-list-wrapper").html(list);
					});
				}
			});
			
			dialog.open();
		};

		/**
		 * i18n Support.
		 * The below methods will enable basic support for i18n
		 */		

		// Default translations (EN):
		this.defaultTranslations = {
			"file_manager": 		"File Manager",
			"upload_title":			"Upload File",
			"rename_title":			"Rename File",
			"remove_title":			"Remove File",
			"mkdir_title":			"Create Directory",
			"upload_action": 		"Upload new file to current directory",
			"mkdir_action": 		"Create new directory",
			"remove_action": 		"Remove this file",
			"rename_action": 		"Rename this file" ,	
			"delete_message": 		"Are you sure you want to delete this file?",
			"new_directory": 		"New Directory",
			"previous_directory": 	"Go to previous directory",
			"rename":				"Rename",
			"select": 				"Select",
			"create": 				"Create",
			"submit": 				"Submit",
			"cancel": 				"Cancel",
			"yes":					"Yes",
			"no":					"No"
		};

		/* Take an html string with placeholders: {{placeholder}} and translate it. 
		 * It takes all labels and trys to translate them. 
		 * If there is no translation (or i18n plugin is not loaded) it will use the defaults.
		 */
		this.i18n = function (tHtml) {
			var map = this.defaultTranslations;

			// If i18n plugin exists:
			if ($.wysiwyg.i18n) {
				$.each(map, function (key, val) {
					map[key] = $.wysiwyg.i18n.t(key, "dialogs.fileManager");
				});
			}

			$.each(map, function (key, val) {
				tHtml = tHtml.replace("{{" + key + "}}", val);
			});

			return tHtml;
		};
	}
})(jQuery);

/**
 * The API for accessing the filemanager.
 *
 * Usage:
 *
 *     filemanager.ls ({path: ''}, function (res) {
 *         if (res.success) {
 *             console.log (res.data);
 *         }
 *     });
 *
 * Methods:
 *
 * - filemanager.ls ({path: ''}, callback);
 * - filemanager.dirs (callback);
 * - filemanager.mkdir ({path: '', name: 'my-folder'}, callback);
 * - filemanager.rm ({path: ''}, callback);
 * - filemanager.mv ({path: 'foo.txt', rename: 'bar.txt'}, callback);
 * - filemanager.prop ({path: 'foo.txt', prop: 'description', value: 'Foo'}, callback);
 */
var filemanager = (function ($) {
	var self = {};
	
	// the prefix for api requests
	self.prefix = '/filemanager/api/';
	
	// enable/disable debugging output to the console
	self.debug = false;
	
	// helper function to verify parameters
	var _has = function (obj, prop) {
		return obj.hasOwnProperty (prop);
	};
	
	// console log wrapper for debugging
	var _log = function (obj) {
		if (self.debug) {
			console.log (obj);
		}
		return obj;
	};

	// List files for a path
	self.ls = function (opts, callback) {
		if (! _has (opts, 'path')) {
			throw new Error ('filemanager.ls() - Missing parameter: path');
		}

		$.get (
			self.prefix + 'ls/' + opts.path,
			callback
		);
	};

	// List all folders
	self.dirs = function (callback) {
		$.get (
			self.prefix + 'dirs',
			callback
		);
	};

	// Make a new folder
	self.mkdir = function (opts, callback) {
		if (! _has (opts, 'path')) {
			throw new Error ('filemanager.mkdir() - Missing parameter: path');
		}

		if (! _has (opts, 'name')) {
			throw new Error ('filemanager.mkdir() - Missing parameter: name');
		}

		$.get (
			self.prefix + 'mkdir/' + opts.path + '/' + opts.name,
			callback
		);
	};

	// Delete a file
	self.rm = function (opts, callback) {
		if (! _has (opts, 'path')) {
			throw new Error ('filemanager.rm() - Missing parameter: path');
		}

		$.get (
			self.prefix + 'rm/' + opts.path,
			callback
		);
	};

	// Rename a file
	self.mv = function (opts, callback) {
		if (! _has (opts, 'path')) {
			throw new Error ('filemanager.mv() - Missing parameter: path');
		}

		if (! _has (opts, 'rename')) {
			throw new Error ('filemanager.mv() - Missing parameter: rename');
		}

		$.get (
			self.prefix + 'rm/' + opts.path
				+ '?rename=' + encodeURIComponent (opts.rename),
			callback
		);
	};

	// Update a property value for a file
	self.prop = function (opts, callback) {
		if (! _has (opts, 'path')) {
			throw new Error ('filemanager.prop() - Missing parameter: path');
		}

		if (! _has (opts, 'prop')) {
			throw new Error ('filemanager.prop() - Missing parameter: prop');
		}

		if (! _has (opts, 'value')) {
			throw new Error ('filemanager.prop() - Missing parameter: value');
		}

		$.get (
			self.prefix + 'prop/' + opts.path
				+ '?prop=' + encodeURIComponent (opts.prop)
				+ '&value=' + encodeURIComponent (opts.value),
			callback
		);
	};

	return self;
})(jQuery);
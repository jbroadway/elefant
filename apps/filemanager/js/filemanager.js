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
 * - filemanager.search ({query: ''}, callback);
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
			self.prefix + 'ls?file=' + encodeURIComponent (opts.path),
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

		$.post (
			self.prefix + 'mkdir',
			{ file: + opts.path + '/' + opts.name },
			callback
		);
	};

	// Delete a file
	self.rm = function (opts, callback) {
		if (! _has (opts, 'path')) {
			throw new Error ('filemanager.rm() - Missing parameter: path');
		}

		$.post (
			self.prefix + 'rm',
			{ file: opts.path },
			callback
		);
	};

	// Delete a folder
	self.rmdir = function (opts, callback) {
		if (! _has (opts, 'path')) {
			throw new Error ('filemanager.rmdir() - Missing parameter: path');
		}

		$.post (
			self.prefix + 'rmdir',
			{ file: opts.path },
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

		$.post (
			self.prefix + 'rm',
			{ file: opts.path, rename: opts.rename },
			callback
		);
	};

	// Move a file into a different folder
	self.drop = function (opts, callback) {
		if (! _has (opts, 'path')) {
			throw new Error ('filemanager.drop() - Missing parameter: path');
		}

		if (! _has (opts, 'folder')) {
			throw new Error ('filemanager.drop() - Missing parameter: folder');
		}

		$.post (
			self.prefix + 'drop',
			{ file: opts.path, folder: opts.folder },
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

		$.post (
			self.prefix + 'prop',
			{ file: opts.path, prop: opts.prop, value: opts.value },
			callback
		);
	};
	
	// Search for files
	self.search = function (opts, callback) {
		if (! _has (opts, 'query')) {
			throw new Error ('filemanager.search() - Missing parameter: query');
		}
		
		$.post (
			self.prefix + 'search',
			{ query: opts.query },
			callback
		);
	};

	return self;
})(jQuery);
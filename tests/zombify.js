// run via node tests/zombify.js

var zombie = require ('zombie');
var assert = require ('assert');

// load the page on www.elefant.lo
zombie.visit ('http://www.elefant.lo/', function (err, browser, status) {
	assert.equal (browser.text ('title'), 'Your Site Name - Welcome to Elefant');
	assert.ok (browser.querySelector ('#head'));
	assert.equal (browser.querySelectorAll ('#slideshow img').length, 4);

	// click on with the text "/admin"
	browser.clickLink ('tr td a', function (err, browser, status) {
		assert.ok (browser.querySelector ('form'));

		// sign in with bad credentials
	});
});

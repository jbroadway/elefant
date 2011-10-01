(function() {
  var assert, zombie;
  zombie = require('zombie');
  assert = require('assert');
  zombie.visit('http://www.elefant.lo/admin', {
    runScripts: false
  }, function(err, browser, status) {
    assert.ok(browser.querySelector('form'), 'should find login form');
    assert.ok(browser.querySelector('p:contains("Please log in to continue.")'), 'should display login message');
    return browser.fill('username', 'you@example.com').fill('password', 'testing').pressButton('Sign in', function(err, browser, status) {
      assert.equal(browser.redirected, true, 'should redirect to /');
      assert.equal(browser.text('title'), 'Your Site Name - Welcome to Elefant');
      return assert.ok(browser.querySelector('script[src="/apps/admin/js/top-bar.js"]'), 'should find top-bar script');
    });
  });
}).call(this);

(function() {
  var assert, zombie;
  zombie = require('zombie');
  assert = require('assert');
  zombie.visit('http://www.elefant.lo/admin', function(err, browser, status) {
    assert.ok(browser.querySelector('form'), 'should find login form');
    assert.ok(browser.querySelector('p:contains("Please log in to continue.")'), 'should display login message');
    return browser.fill('username', 'you@example.com').fill('password', 'fake password').pressButton('Sign in', function(err, browser, status) {
      return assert.ok(browser.querySelector('p:contains("Incorrect email or password, please try again.")'), 'should display login error');
    });
  });
}).call(this);

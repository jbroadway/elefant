(function() {
  var assert, zombie;
  zombie = require('zombie');
  assert = require('assert');
  zombie.visit('http://www.elefant.lo/', function(err, br, status) {
    assert.equal(br.text('title'), 'Your Site Name - Welcome to Elefant');
    assert.ok(br.querySelector('#head'), '#head element expected');
    return assert.equal(br.querySelectorAll('#slideshow img').length, 4, 'should find 4 slideshow images');
  });
}).call(this);

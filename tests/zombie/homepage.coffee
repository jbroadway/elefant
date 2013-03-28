# zombie.js tests for elefant
# 1. compile to javascript via `coffee -c tests/zombie/*.coffee`
# 2. run via `node tests/zombie/*.js`

zombie = require('zombie')
assert = require('assert')

zombie.visit 'http://www.elefant.lo/', (err, br, status)->
	# page loaded ok
	assert.equal br.text('title'), 'Your Site Name - Welcome to Elefant'
	assert.ok br.querySelector('#head'), '#head element expected'
		
	# dynamic include for slideshow working
	assert.equal br.querySelectorAll('#slideshow img').length, 4, 'should find 4 slideshow images'

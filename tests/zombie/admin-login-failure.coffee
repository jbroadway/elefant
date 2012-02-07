# zombie.js tests for elefant
# 1. compile to javascript via `coffee -c tests/zombie/*.coffee`
# 2. run via `node tests/zombie/*.js`

zombie = require('zombie')
assert = require('assert')

zombie.visit 'http://www.elefant.lo/admin', (err, browser, status)->
	# page loaded ok
	assert.ok browser.querySelector('form'), 'should find login form'
	assert.ok browser.querySelector('p:contains("Please log in to continue.")'), 'should display login message'
	
	# verify login error with fake credentials
	browser
	.fill('username', 'you@example.com')
	.fill('password', 'fake password')
	.pressButton 'Sign in', (err, browser, status)->
		# verify login error
		assert.ok browser.querySelector('p:contains("Incorrect email or password, please try again.")'), 'should display login error'

# zombie.js tests for elefant
# 1. compile to javascript via `coffee -c tests/zombie/*.coffee`
# 2. run via `node tests/zombie/*.js`

zombie = require('zombie')
assert = require('assert')

zombie.visit 'http://www.elefant.lo/admin', {runScripts:false}, (err, browser, status)->
	# page loaded ok
	assert.ok browser.querySelector('form'), 'should find login form'
	assert.ok browser.querySelector('p:contains("Please log in to continue.")'), 'should display login message'
		
	# now verify proper credentials succeed
	browser
	.fill('username', 'you@example.com')
	.fill('password', 'testing')
	.pressButton 'Sign in', (err, browser, status)->
		# verify success
		assert.equal browser.redirected, true, 'should redirect to /'
		assert.equal browser.text('title'), 'Your Site Name - Welcome to Elefant'
		
		# verify top-bar script loaded
		assert.ok browser.querySelector('script[src="/apps/admin/js/top-bar.js"]'), 'should find top-bar script'

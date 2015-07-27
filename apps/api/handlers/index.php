<?php

/**
 * Default handler, simply forwards to the current version of the API.
 */

$this->redirect ('/api/' . Appconf:api('Api','current_version'));

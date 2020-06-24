<?php

$this->require_acl ('admin', 'blocks');

$this->restful (new blocks\API ());

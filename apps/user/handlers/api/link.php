<?php

$this->require_acl ('admin', 'user');

$this->restful (new user\API\Link);

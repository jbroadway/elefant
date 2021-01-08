<?php

$this->require_acl ('admin', 'admin/edit', 'blocks');

$this->restful (new blocks\API ());

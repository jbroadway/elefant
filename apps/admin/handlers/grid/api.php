<?php

$this->require_acl ('admin', 'admin/edit');

$this->restful (new admin\Grid\API);

<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * To use the new preload capabilities introduced in PHP 7.4, add the following
 * to your php.ini:
 *
 *     opcache.preload=/path/to/project/preload.php
 *
 * For more info, see:
 *
 * https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.preload
 */
require_once'lib/FrontController.php';
require_once'conf/version.php';
require_once'lib/Autoloader.php';
require_once'lib/Functions.php';
require_once'lib/Debugger.php';
require_once'lib/DB.php';
require_once'lib/Page.php';
require_once'lib/I18n.php';
require_once'lib/Controller.php';
require_once'lib/Template.php';
require_once'lib/View.php';
require_once'lib/Acl.php';
require_once'lib/Appconf.php';
require_once'lib/Model.php';
require_once'lib/ExtendedModel.php';
require_once'lib/Validator.php';
require_once'lib/Product.php';
require_once'lib/Ini.php';
require_once'lib/Restful.php';
require_once 'lib/Page.php';
require_once 'lib/Form.php';
require_once 'apps/admin/lib/Lock.php';
require_once 'apps/admin/models/Webpage.php';
require_once 'apps/blocks/models/Block.php';
require_once 'apps/blog/models/Post.php';
require_once 'apps/filemanager/lib/FileManager.php';
require_once 'apps/filemanager/lib/Image.php';
require_once 'apps/navigation/lib/Tree.php';
require_once 'apps/navigation/lib/Link.php';
require_once 'apps/navigation/lib/Navigation.php';
require_once 'apps/user/models/User.php';
require_once 'apps/user/models/Session.php';
require_once 'lib/vendor/URLify.php';

if (file_exists ('bootstrap.php')) {
	require_once 'bootstrap.php';
}

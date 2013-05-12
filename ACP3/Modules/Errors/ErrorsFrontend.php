<?php

namespace ACP3\Modules\Errors;

use ACP3\Core;

/**
 * Description of ErrorsFrontend
 *
 * @author Tino
 */
class ErrorsFrontend extends Core\ModuleController {

	public function __construct($injector)
	{
		parent::__construct($injector);
	}

	public function action403()
	{
		header('HTTP/1.0 403 Forbidden');
	}

	public function action404()
	{
		header('HTTP/1.0 404 not found');
	}

}
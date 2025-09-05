<?php

/**
 * Nextcloud - Excalidraw
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2022
 */

namespace OCA\Excalidraw\Controller;

use OCA\Excalidraw\AppInfo\Application;
use OCP\AppFramework\Controller;

use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;

use Psr\Log\LoggerInterface;

class ConfigController extends Controller {

	public function __construct(
		private string $AppName,
		IRequest $request,
		private IConfig $config,
		private LoggerInterface $logger,
		private ?string $userId) {
		parent::__construct($AppName, $request);
	}

	/**
	 * set admin config values
	 *
	 * @param array $values
	 * @return DataResponse
	 */
	public function setAdminConfig(array $values): DataResponse {
		foreach ($values as $key => $value) {
			$this->config->setAppValue(Application::APP_ID, $key, $value);
		}
		$response = new DataResponse(1);
		return $response;
	}
}

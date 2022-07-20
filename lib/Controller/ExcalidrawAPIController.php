<?php
/**
 * Nextcloud - Excalidraw integration
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2022
 */

namespace OCA\Excalidraw\Controller;

use OCA\Excalidraw\Service\ExcalidrawAPIService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use Psr\Log\LoggerInterface;
use OCP\AppFramework\Controller;
use OCP\IRequest;


class ExcalidrawAPIController extends Controller {

	/**
	 * @var string|null
	 */
	private $userId;
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	/**
	 * @var IConfig
	 */
	private $config;
	/**
	 * @var ExcalidrawAPIService
	 */
	private $excalidrawAPIService;

	public function __construct(string            $appName,
								IRequest          $request,
								IConfig           $config,
								LoggerInterface   $logger,
								ExcalidrawAPIService  $excalidrawAPIService,
								?string           $userId) {
		parent::__construct($appName, $request);
		$this->userId = $userId;
		$this->logger = $logger;
		$this->config = $config;
		$this->excalidrawAPIService = $excalidrawAPIService;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param string $name
	 * @return DataResponse
	 */
	public function newBoard(string $name): DataResponse {
		$result = $this->excalidrawAPIService->newBoard($this->userId, $name);
		if (isset($result['error'])) {
			return new DataResponse($result, Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse($result);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function getBoards(): DataResponse {
		$result = $this->excalidrawAPIService->getBoards($this->userId);
		if (isset($result['error'])) {
			return new DataResponse($result, Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse($result);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function deleteBoard(string $boardId): DataResponse {
		$result = $this->excalidrawAPIService->deleteBoard($this->userId, $boardId);
		if (isset($result['error'])) {
			return new DataResponse($result, Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse($result);
	}
}

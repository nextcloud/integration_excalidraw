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
use OCP\App\IAppManager;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use Psr\Log\LoggerInterface;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

use OCA\Excalidraw\AppInfo\Application;

class PageController extends Controller {

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
	 * @var IAppManager
	 */
	private $appManager;
	/**
	 * @var IInitialState
	 */
	private $initialStateService;
	/**
	 * @var ExcalidrawAPIService
	 */
	private $excalidrawAPIService;

	public function __construct(string $appName,
								IRequest $request,
								IConfig $config,
								IAppManager $appManager,
								IInitialState $initialStateService,
								LoggerInterface $logger,
								ExcalidrawAPIService $excalidrawAPIService,
								?string $userId) {
		parent::__construct($appName, $request);
		$this->userId = $userId;
		$this->logger = $logger;
		$this->config = $config;
		$this->appManager = $appManager;
		$this->initialStateService = $initialStateService;
		$this->excalidrawAPIService = $excalidrawAPIService;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function index(): TemplateResponse {
		$baseUrl = $this->excalidrawAPIService->getBaseUrl($this->userId);
		$talkEnabled = $this->appManager->isEnabledForUser('spreed', $this->userId);
		$pageInitialState = [
			'base_url' => $baseUrl,
			'talk_enabled' => $talkEnabled,
			'board_list' => $this->excalidrawAPIService->getBoards($this->userId),
		];
		$this->initialStateService->provideInitialState('excalidraw-state', $pageInitialState);
		return new TemplateResponse(Application::APP_ID, 'main', []);
	}
}

<?php
namespace OCA\Excalidraw\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IRequest;
use OCP\IConfig;
use OCP\Settings\ISettings;

use OCA\Excalidraw\AppInfo\Application;

class Admin implements ISettings {

	private $config;

	public function __construct(string $appName,
								IRequest $request,
								IConfig $config,
								IInitialState $initialStateService,
								?string $userId) {
		$this->appName = $appName;
		$this->request = $request;
		$this->config = $config;
		$this->initialStateService = $initialStateService;
		$this->userId = $userId;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$baseUrl = $this->config->getAppValue(Application::APP_ID, 'base_url', Application::DEFAULT_BASE_URL) ?: Application::DEFAULT_BASE_URL;
		$overrideLinkClick = $this->config->getAppValue(Application::APP_ID, 'override_link_click') === '1';

		$adminConfig = [
			'base_url' => $baseUrl,
			'override_link_click' => $overrideLinkClick,
		];
		$this->initialStateService->provideInitialState('admin-config', $adminConfig);
		return new TemplateResponse(Application::APP_ID, 'adminSettings');
	}

	public function getSection(): string {
		return 'connected-accounts';
	}

	public function getPriority(): int {
		return 10;
	}
}

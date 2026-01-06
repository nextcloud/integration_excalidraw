<?php

namespace OCA\Excalidraw\Settings;

use OCA\Excalidraw\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\IRequest;

use OCP\Settings\ISettings;

class Admin implements ISettings {
	public function __construct(
		private string $appName,
		private IRequest $request,
		private IConfig $config,
		private IInitialState $initialStateService,
		private ?string $userId,
	) {
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

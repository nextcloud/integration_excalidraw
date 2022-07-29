<?php
/**
 * Nextcloud - Excalidraw
 *
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2022
 */

namespace OCA\Excalidraw\AppInfo;

use OCA\Excalidraw\Listener\AddContentSecurityPolicyListener;
use OCA\Excalidraw\Listener\LoadFileScriptListener;
use OCA\Viewer\Event\LoadViewer;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;

use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;
use OCP\Util;

/**
 * Class Application
 *
 * @package OCA\Excalidraw\AppInfo
 */
class Application extends App implements IBootstrap {

	public const APP_ID = 'integration_excalidraw';
	public const DEFAULT_BASE_URL = 'https://excalidraw.com';

	/**
	 * Constructor
	 *
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
//		$context->registerEventListener(LoadAdditionalScriptsEvent::class, LoadFileScriptListener::class);
		$context->registerEventListener(AddContentSecurityPolicyEvent::class, AddContentSecurityPolicyListener::class);
	}

	public function boot(IBootContext $context): void {
		$context->injectFn(function (
			IInitialState $initialState,
			IConfig $config,
			?string $userId
		) {
			/*
			if (!$userId) {
				return;
			}
			*/

			$initialState->provideLazyInitialState('base_url', function () use ($config) {
				return $config->getAppValue(self::APP_ID, 'base_url', self::DEFAULT_BASE_URL) ?: self::DEFAULT_BASE_URL;
			});
			$initialState->provideLazyInitialState('override_link_click', function () use ($config) {
				return $config->getAppValue(self::APP_ID, 'override_link_click') === '1';
			});
			Util::addScript(self::APP_ID, self::APP_ID . '-standalone');
		});
	}
}


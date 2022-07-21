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
use OCA\Excalidraw\Listener\LoadViewerListener;
use OCA\Viewer\Event\LoadViewer;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;

use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

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
//		$context->registerEventListener(LoadViewer::class, LoadViewerListener::class);
		$context->registerEventListener(AddContentSecurityPolicyEvent::class, AddContentSecurityPolicyListener::class);
	}

	public function boot(IBootContext $context): void {
	}
}


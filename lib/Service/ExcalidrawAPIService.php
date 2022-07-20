<?php
/**
 * Nextcloud - Excalidraw
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier
 * @copyright Julien Veyssier 2022
 */

namespace OCA\Excalidraw\Service;

use OCA\Excalidraw\AppInfo\Application;
use OCA\Excalidraw\Db\Board;
use OCA\Excalidraw\Db\BoardMapper;
use OCP\IConfig;
use Psr\Log\LoggerInterface;
use OCP\Http\Client\IClientService;

class ExcalidrawAPIService {
	public const BOARD_ID_CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyz';
	public const BOARD_ID_LENGTH = 20;
	public const BOARD_KEY_CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-';
	public const BOARD_KEY_LENGTH = 22;
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	/**
	 * @var IConfig
	 */
	private $config;
	/**
	 * @var string
	 */
	private $appName;
	/**
	 * @var BoardMapper
	 */
	private $boardMapper;

	/**
	 * Service to make requests to Excalidraw API
	 */
	public function __construct (string $appName,
								LoggerInterface $logger,
								IConfig $config,
								BoardMapper $boardMapper,
								IClientService $clientService) {
		$this->client = $clientService->newClient();
		$this->logger = $logger;
		$this->config = $config;
		$this->appName = $appName;
		$this->boardMapper = $boardMapper;
	}

	private function randomString(string $characters, int $length): string {
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function generateBoard(): array {
		return [
			'boardId' => $this->randomString(self::BOARD_ID_CHARACTERS, self::BOARD_ID_LENGTH),
			'boardKey' => $this->randomString(self::BOARD_KEY_CHARACTERS, self::BOARD_KEY_LENGTH),
		];
	}

	public function newBoard(string $userId, string $name): array {
		$boardInfo = $this->generateBoard();
		$this->boardMapper->createBoard($userId, $boardInfo['boardId'], $boardInfo['boardKey'], $name);
		return [
			'id' => $boardInfo['boardId'],
			'key' => $boardInfo['boardKey'],
		];
	}

	public function deleteBoard(string $userId, string $boardId): array {
		$this->boardMapper->deleteFromRemoteId($userId, $boardId);
		return [];
	}

	public function editBoard(string $userId, string $boardId, string $name): array {
		$board = $this->boardMapper->findBoardByRemoteId($userId, $boardId);
		$board->setName($name);
		$this->boardMapper->update($board);
		return [];
	}

	public function getBoards(string $userId): array {
		$dbBoards = $this->boardMapper->getBoards($userId);
		return array_map(static function(Board $dbBoard): array {
			return [
				'id' => $dbBoard->getBoardId(),
				'key' => $dbBoard->getBoardKey(),
				'name' => $dbBoard->getName(),
				'trash' => false,
			];
		}, $dbBoards);
	}

	public function getBaseUrl(string $userId): string {
		$adminBaseUrl = $this->config->getAppValue(Application::APP_ID, 'base_url', Application::DEFAULT_BASE_URL) ?: Application::DEFAULT_BASE_URL;
		return $this->config->getUserValue($userId, Application::APP_ID, 'base_url', $adminBaseUrl) ?: $adminBaseUrl;
	}
}

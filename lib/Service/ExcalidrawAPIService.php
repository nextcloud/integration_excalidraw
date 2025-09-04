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
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\Security\ISecureRandom;
use Psr\Log\LoggerInterface;

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
	 * @var ISecureRandom
	 */
	private $random;

	/**
	 * Service to make requests to Excalidraw API
	 */
	public function __construct(string $appName,
		LoggerInterface $logger,
		IConfig $config,
		BoardMapper $boardMapper,
		ISecureRandom $random,
		IClientService $clientService) {
		$this->client = $clientService->newClient();
		$this->logger = $logger;
		$this->config = $config;
		$this->boardMapper = $boardMapper;
		$this->random = $random;
	}

	/**
	 * @return array
	 */
	public function generateBoard(): array {
		return [
			'boardId' => $this->random->generate(self::BOARD_ID_LENGTH, ISecureRandom::CHAR_LOWER . ISecureRandom::CHAR_DIGITS),
			'boardKey' => $this->random->generate(self::BOARD_KEY_LENGTH, '_-' . ISecureRandom::CHAR_UPPER . ISecureRandom::CHAR_LOWER . ISecureRandom::CHAR_DIGITS),
		];
	}

	/**
	 * @param string $userId
	 * @param string $name
	 * @return array
	 * @throws \OCP\DB\Exception
	 */
	public function newBoard(string $userId, string $name): array {
		$boardInfo = $this->generateBoard();
		$this->boardMapper->createBoard($userId, $boardInfo['boardId'], $boardInfo['boardKey'], $name);
		return [
			'id' => $boardInfo['boardId'],
			'key' => $boardInfo['boardKey'],
		];
	}

	/**
	 * @param string $userId
	 * @param string $boardId
	 * @return array
	 * @throws \OCP\DB\Exception
	 */
	public function deleteBoard(string $userId, string $boardId): array {
		$this->boardMapper->deleteFromRemoteId($userId, $boardId);
		return [];
	}

	/**
	 * @param string $userId
	 * @param string $boardId
	 * @param string $name
	 * @return array
	 * @throws \OCP\AppFramework\Db\DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws \OCP\DB\Exception
	 */
	public function editBoard(string $userId, string $boardId, string $name): array {
		$board = $this->boardMapper->findBoardByRemoteId($userId, $boardId);
		$board->setName($name);
		$this->boardMapper->update($board);
		return [];
	}

	/**
	 * @param string $userId
	 * @return array
	 */
	public function getBoards(string $userId): array {
		$dbBoards = $this->boardMapper->getBoards($userId);
		return array_map(static function (Board $dbBoard): array {
			return [
				'id' => $dbBoard->getBoardId(),
				'key' => $dbBoard->getBoardKey(),
				'name' => $dbBoard->getName(),
				'trash' => false,
			];
		}, $dbBoards);
	}

	/**
	 * @param string $userId
	 * @return string
	 */
	public function getBaseUrl(string $userId): string {
		$adminBaseUrl = $this->config->getAppValue(Application::APP_ID, 'base_url', Application::DEFAULT_BASE_URL) ?: Application::DEFAULT_BASE_URL;
		return $this->config->getUserValue($userId, Application::APP_ID, 'base_url', $adminBaseUrl) ?: $adminBaseUrl;
	}
}

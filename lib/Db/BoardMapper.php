<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2022, Julien Veyssier <eneiluj@posteo.net>
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Excalidraw\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;

use OCP\IDBConnection;

/**
 * @extends QBMapper<Board>
 */
class BoardMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'excalidraw_boards', Board::class);
	}

	/**
	 * Find board by board_id (remote ID)
	 *
	 * @param string $boardId
	 * @return Entity
	 * @psalm-return Board
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws \OCP\DB\Exception
	 */
	public function findBoardByRemoteId(string $userId, string $boardId): Board {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR))
			)
			->andWhere(
				$qb->expr()->eq('board_id', $qb->createNamedParameter($boardId, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntity($qb);
	}

	/**
	 * @param string $userId
	 * @param string $boardId
	 * @return int
	 * @throws \OCP\DB\Exception
	 */
	public function deleteFromRemoteId(string $userId, string $boardId): int {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR))
			)
			->andWhere(
				$qb->expr()->eq('board_id', $qb->createNamedParameter($boardId, IQueryBuilder::PARAM_STR))
			);
		return $qb->executeStatement();
	}

	/**
	 * @return Board[]
	 */
	public function getBoards(string $userId) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntities($qb);
	}

	/**
	 * Create a board
	 *
	 * @param string $userId
	 * @param string $boardId
	 * @param string $boardKey
	 * @param string $name
	 * @return mixed|Board|\OCP\AppFramework\Db\Entity
	 * @throws \OCP\DB\Exception
	 */
	public function createBoard(string $userId, string $boardId, string $boardKey, string $name) {
		try {
			// do not create if one with same sid already exists (which should not happen)
			return $this->findBoardByRemoteId($userId, $boardId);
		} catch (MultipleObjectsReturnedException $e) {
			// this can't happen
			return null;
		} catch (DoesNotExistException $e) {
		}

		$board = new Board();
		$board->setUserId($userId);
		$board->setBoardId($boardId);
		$board->setBoardKey($boardKey);
		$board->setName($name);
		return $this->insert($board);
	}
}

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

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getBoardId()
 * @method void setBoardId(string $boardId)
 * @method string getBoardKey()
 * @method void setBoardKey(string $boardKey)
 * @method string getName()
 * @method void setName(string $name)
 */
class Board extends Entity implements \JsonSerializable {

	/** @var string */
	protected $userId;

	/** @var string */
	protected $boardId;

	/** @var string */
	protected $boardKey;

	/** @var string */
	protected $name;

	public function __construct() {
		$this->addType('user_id', 'string');
		$this->addType('board_id', 'string');
		$this->addType('board_key', 'string');
		$this->addType('name', 'string');
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'user_id' => $this->userId,
			'board_id' => $this->boardId,
			'board_key' => $this->boardKey,
			'name' => $this->name,
		];
	}
}

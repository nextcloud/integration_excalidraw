<?php

/**
 * Nextcloud - excalidraw
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2022
 */

return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

		['name' => 'excalidrawAPI#getBoards', 'url' => '/boards', 'verb' => 'GET'],
		['name' => 'excalidrawAPI#newBoard', 'url' => '/board', 'verb' => 'POST'],
		['name' => 'excalidrawAPI#editBoard', 'url' => '/board/{boardId}', 'verb' => 'PUT'],
		['name' => 'excalidrawAPI#deleteBoard', 'url' => '/board/{boardId}', 'verb' => 'DELETE'],

		['name' => 'config#setAdminConfig', 'url' => '/admin-config', 'verb' => 'PUT'],
	]
];

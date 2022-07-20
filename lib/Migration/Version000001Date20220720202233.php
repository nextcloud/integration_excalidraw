<?php

declare(strict_types=1);

namespace OCA\Excalidraw\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version000001Date20220720202233 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {

		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$table = $schema->createTable('excalidraw_boards');
		$table->addColumn('id', Types::INTEGER, [
			'autoincrement' => true,
			'notnull' => true,
			'length' => 4,
		]);
		$table->addColumn('user_id', Types::STRING, [
			'notnull' => true,
			'length' => 256,
		]);
		$table->addColumn('board_id', Types::STRING, [
			'notnull' => true,
			'length' => 256,
		]);
		$table->addColumn('board_key', Types::STRING, [
			'notnull' => true,
			'length' => 256,
		]);
		$table->addColumn('name', Types::STRING, [
			'notnull' => true,
			'length' => 256,
		]);
		$table->setPrimaryKey(['id']);
		$table->addUniqueIndex(['board_id']);

		return $schema;
	}
}

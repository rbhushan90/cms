<?php
/**
 * Licensed under The GPL-3.0 License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since	 2.0.0
 * @author	 Christopher Castro <chris@quickapps.es>
 * @link	 http://www.quickappscms.org
 * @license	 http://opensource.org/licenses/gpl-3.0.html GPL-3.0 License
 */
namespace Node\Model\Table;

use Cake\Database\Schema\Table as Schema;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Node\Model\Entity\Node;

/**
 * Represents "node_revisions" database table.
 *
 */
class NodeRevisionsTable extends Table {

/**
 * Alter the schema used by this table.
 *
 * @param \Cake\Database\Schema\Table $table The table definition fetched from database
 * @return \Cake\Database\Schema\Table the altered schema
 */
	protected function _initializeSchema(Schema $table) {
		$table->columnType('data', 'serialized');
		return $table;
	}

/**
 * Initialize a table instance. Called after the constructor.
 *
 * @param array $config Configuration options passed to the constructor
 * @return void
 */
	public function initialize(array $config) {
		$this->addBehavior('Timestamp');
	}

/**
 * Attaches NodeType information to each node revision.
 * 
 * @param \Cake\Event\Event $event The event that was triggered
 * @param \Cake\ORM\Query $query The query object
 * @param array $options Additional options given as an array
 * @param bool $primary Whether this find is a primary query or not
 * @return void
 */
	public function beforeFind(Event $event, Query $query, array $options, $primary) {
		$query->formatResults(function ($results) {
			return $results->map(function ($revision) {
				try {
					if (isset($revision->data->node_type_id)) {
						$nodeType = TableRegistry::get('Node.NodeTypes')
							->find()
							->where(['id' => $revision->data->node_type_id])
							->first();
						$revision->data->set('node_type', $nodeType);
					}
				} catch (\Exception $e) {
					$revision->data->set('node_type', false);
				}
				return $revision;
			});
		});

		return $query;
	}

}

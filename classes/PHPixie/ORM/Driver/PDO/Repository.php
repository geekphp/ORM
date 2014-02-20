<?php

namespace PHPixie\ORM\Driver\PDO;

class Repository extends \PHPixie\ORM\Repository {
	
	protected $table;
	protected $id_field;
	
	public function __construct($db, $model_name, $plural_name, $config) {
		parent::__construct($model_name, $plural_name, $config);
		$this->table = $config->get('table', $plural_name)
		$this->id_field  = $config->get('id_field', 'id');
	}
	
	public function db_query($type) {
		return $this->connection()
					->query($type)
					->table($this->table);
	}
	
	public function id_field() {
		return $this->id_field;
	}
}
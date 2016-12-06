<?php
/**
 * 
 * Socialloft
 * 
 * Moosocial team
 * 
 */

App::uses('Mysql', 'Model/Datasource/Database');

class Moosql extends Mysql {
	public $mooFieldSeparator = null;
	
	public function fetchResult() {
		if ($row = $this->_result->fetch(PDO::FETCH_NUM)) {
			$resultRow = array();
			foreach ($this->map as $col => $meta) {
				list($table, $column, $type) = $meta;
				$resultRow[$table][$column] = $row[$col];
				if ($type === 'boolean' && $row[$col] !== null) {
					$resultRow[$table][$column] = $this->boolean($resultRow[$table][$column]);
				}
			}		
			$array_key = @array_keys($resultRow);
			
			if (is_array($array_key) && count($array_key))
			{
				foreach ($array_key as $alias)
				{
					$Model = ClassRegistry::getObject($alias);
					if ($Model instanceof AppModel)
					{
						$mooFieldSeparator = $Model->getMooFields();
						
						if ($mooFieldSeparator && count($mooFieldSeparator))
						{										
							foreach ($mooFieldSeparator as $mooField)
							{
								$method = 'get'.ucfirst(strtolower($mooField));
								if ($Model instanceof  AppModel && method_exists($Model,$method))
								{
									$resultRow[$alias]['moo_'.$mooField] = $Model->{$method}($resultRow[$alias]);
								}
							}
						}
					}
				}
			}
					
			return $resultRow;
		}
		$this->_result->closeCursor();
		return false;
	}
}
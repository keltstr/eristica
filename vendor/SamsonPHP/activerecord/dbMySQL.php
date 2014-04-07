<?php
namespace samson\activerecord;

/**
 * Класс описывающий работу с MySQL
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @author Nikita Kotenko <nick.w2r@gmail.com>
 *
 */
class dbMySQL extends dbMySQLConnector implements idb 
{	
	/**
	 * Количество запросов класса
	 * @var integer
	 */
	private $query_count = 0;	
	
	/**
	 * Количество затраченного времени на запросы к БД
	 * @var integer
	 */
	private $elapsed = 0;
	
	/** Show hide query debug information */
	public function debug( $flag = true )
	{
		if( $flag ) $_SESSION['__AR_SHOW_QUERY__'] = true;
		else unset($_SESSION['__AR_SHOW_QUERY__']);
	}

	/**
	 * @see idb::simple_query()
	 */
	public function & simple_query( $sql )
	{
		// Если мы подключены к БД
		if( ! $this->connected ) return e('Подключение к БД не было выполнено', E_SAMSON_FATAL_ERROR );
					
		// Выполним запрос к БД
		$resource = mysql_query( $sql, $this->link ) or e( mysql_error( $this->link ), E_SAMSON_SQL_ERROR );	

		// Если нужно то выведем запрос
		if( isset($_SESSION['__AR_SHOW_QUERY__']) ) elapsed($sql);

		// Веренм указатель на полученный ресурс
		return $resource;
	}
		
	/**
	 * @see idb::query()
	 */
	public function & query( $sql )
	{
		// Если мы подключены к БД
		if( !$this->connected ) return e('Подключение к БД не было выполнено', E_SAMSON_SQL_ERROR );
		
		// Создадим коллекцию
		$rows = array();
		
		// Сохраним отметку времени для расчетов
		$t_last = microtime( TRUE );	

		//echo($sql."\n");
			
		// Выполним запрос к БД
		$sql_result = mysql_query( $sql, $this->link ) or e( mysql_error( $this->link ), E_SAMSON_SQL_ERROR );		
			
		// Если нужно то выведем запрос
		if( isset($_SESSION['__AR_SHOW_QUERY__']) )elapsed($sql);
		
		// Если нам вернулся ресурс
		if( !is_bool($sql_result) )
		{
			// Заполним все результаты
			while( $row = mysql_fetch_array( $sql_result, MYSQL_ASSOC ) ) $rows[] = $row;				

			// Очистим память
			mysql_free_result( $sql_result );			
		}
			
		// Увеличим счетких запросов для статистики
		$this->query_count++;			
		
		// Отметим затраченное время на выполнение запроса
		$this->elapsed += microtime( TRUE ) - $t_last;
			
		// Вернем строки данных из БД
		return $rows;					
	}
		
	/** @see idb::create() */
	public function create( $class_name, idbRecord & $object = NULL )
	{			
		// Получим переменные для запроса
		extract($this->__get_table_data( $class_name ));		
		
		// Соберем две коллекции
		$fields = $this->getQueryFields( $class_name, $object );	
		
		// Выполним запрос на создание новой записи в БД 
		$this->query( 'INSERT INTO `'.$_table_name.'` (`'.implode( '`,`', array_keys( $fields ) ).'`) VALUES ('.implode( ',', $fields ).')' ); 
		
		// Вернем идентификатор новосозданной записи в БД
		return mysql_insert_id( $this->link );		
	}
	
	/** @see idb::update() */
	public function update( $class_name, idbRecord & $object )
	{		
		// Получим переменные для запроса
		extract($this->__get_table_data( $class_name ));			
		
		// Соберем две коллекции
		$fields = $this->getQueryFields( $class_name, $object, TRUE );
		
		// Выполним запрос на обновление записи в БД
		$this->query( 'UPDATE `'.$_table_name.'` SET '.implode( ',', $fields ).' WHERE '.$_table_name.'.'.$_primary.'="'.$object->id.'"' );
		
		// Дошли сюда - значит все ок
		return TRUE;
	}
	
	/** @see idb::delete() */
	public function delete( $class_name, idbRecord & $object )
	{
		// Получим переменные для запроса
		extract($this->__get_table_data( $class_name ));
	
		// Выполним прямой запрос на удаление записи
		return $this->query( 'DELETE FROM `'.$_table_name.'` WHERE '.$_primary.' = "' . $object->id . '"', FALSE );
	}
	
	// TODO: Очень узкое место для совместимости с 5.2 !!!
	/**
	 * Обратная совместить с PHP < 5.3 т.к. там нельзя подставлять переменное имя класса
	 * в статическом контексте 
	 * @param unknown_type $class_name
	 */
	public function __get_table_data( $class_name )
	{	
		// Remove table prefix
		$class_name = str_replace(self::$prefix, '', $class_name);
		
		// Сформируем правильное имя класса
		$class_name = ns_classname( $class_name, 'samson\activerecord');
		
		// Сформируем комманды на получение статических переменных определенного класса		
		$_table_name 	= '$_table_name = '.$class_name.'::$_table_name;'; 
		$_own_group 	= '$_own_group = '.$class_name.'::$_own_group;';
		$_table_attributes	= '$_table_attributes = '.$class_name.'::$_table_attributes;';
		$_primary 		= '$_primary = '.$class_name.'::$_primary;'; 
		$_sql_from 		= '$_sql_from = '.$class_name.'::$_sql_from;';
		$_sql_select	= '$_sql_select = '.$class_name.'::$_sql_select;';
		$_attributes	= '$_attributes = '.$class_name.'::$_attributes;';
		$_types			= '$_types = '.$class_name.'::$_types;';
		$_map			= '$_map = '.$class_name.'::$_map;';
		$_relations		= '$_relations = '.$class_name.'::$_relations;';
		$_unique		= '$_unique = '.$class_name.'::$_unique;';
		$_relation_type	= '$_relation_type = '.$class_name.'::$_relation_type;';
		$_relation_alias= '$_relation_alias = '.$class_name.'::$_relation_alias;';
		
		//trace($_table_name.$_primary.$_sql_from.$_sql_select.$_map.$_attributes.$_relations.$_relation_type.$_types.$_unique);
		
		// Выполним специальный код получения значений переменной
		eval( $_own_group.$_table_name.$_primary.$_sql_from.$_sql_select.$_map.$_attributes.$_relations.$_relation_type.$_relation_alias.$_types.$_unique.$_table_attributes );
		
		// Вернем массив имен переменных и их значений
		return array
		(		
			'_table_name' 		=> $_table_name,	
			'_own_group'		=> $_own_group,		
			'_primary' 			=> $_primary,
			'_attributes' 		=> $_attributes,
			'_table_attributes' => $_table_attributes,
			'_types' 			=> $_types,
			'_map' 				=> $_map,
			'_relations' 		=> $_relations,
			'_relation_type' 	=> $_relation_type,
			'_relation_alias'	=> $_relation_alias,
			'_sql_from' 		=> $_sql_from,
			'_sql_select' 		=> $_sql_select,
			'_unique'			=> $_unique,			
		);	
	}
	
	/** Count query result */
	public function count( $class_name, dbQuery $query )
	{
		// Get SQL
		$sql = 'SELECT Count(*) as __Count FROM ('.$this->prepareSQL( $class_name, $query ).') as __table';
		// Выполним запрос к БД
		$db_data = $this->query( $sql );
			
		return $db_data[0]['__Count'];
	}
	
	/** Count query result */
	public function innerCount( $class_name, dbQuery $query )
	{		
		$params = $this->__get_table_data( $class_name );
	
		// Get SQL
		$sql = 'SELECT Count(*) as __Count FROM ('.$this->prepareInnerSQL( $class_name, $query, $params).') as __table';
		
		// Выполним запрос к БД
		//$this->debug();		
		$db_data = $this->query( $sql );
		//$this->debug(false);
			
		return $db_data[0]['__Count'];
	}
	/**
	 * @see idb::find()
	 */
	public function & find( $class_name, dbQuery $query )
	{
		// Get SQL
		$sql = $this->prepareSQL($class_name, $query);

		// Выполним запрос к БД					
		$db_data = $this->query( $sql );
		
			
		// Результат выполнения запроса
		$result = array();
		
		//trace($query->virtual_fields);
		
		// Выполним запрос к БД и создадим объекты
		if ( ( is_array( $db_data ) ) && ( sizeof($db_data) > 0 ) ) 
		{
			$result = $this->toRecords( $class_name, $db_data, $query->join, array_merge( $query->own_virtual_fields, $query->virtual_fields) );
		}
	
		// Вернем коллекцию полученных объектов
		return $result;
	}
	
	/**	 
	 * @see idb::find_by_id()
	 */
	public function & find_by_id( $class_name, $id )
	{			
		// Получим переменные для запроса
		extract($this->__get_table_data( $class_name ));		
		
		// Выполним запрос к БД
		$record_data = $this->query('SELECT '.$_sql_select['this'].' FROM '.$_sql_from['this'].' WHERE '.$_table_name.'.'.$_primary.' = "'.$id.'"');

		// Если запрос выполнился успешно и получена минимум 1-на запись из БД - создадим объект-запись из неё
		$db_records = $this->toRecords( $class_name, $record_data );

		// Переменная для возврата
		$ret = null;
		
		// Если мы получили 1ю запись то вернем её
		if( sizeof($db_records) >= 1 ) $ret = array_shift($db_records);		
		
		// Вернем переменную
		return $ret;
	}
	
	
	/**
	 * Generic database migration handler
	 * @param string $classname Class for searching migration methods
	 * @param string $version_handler External handler for interacting with database version
	 */
	public function migration( $classname, $version_handler )
	{		
		if( !is_callable( $version_handler ) ) return e('No version handler is passed', E_SAMSON_ACTIVERECORD_ERROR);
		
		// Get current database version
		$version = call_user_func( $version_handler );
		
		// DB vesion migrating mechanism
		foreach( get_class_methods( $classname ) as $m )
		{
			// Parse migration method name to get migrating versions
			if( preg_match('/^migrate_(?<from>\d+)_to_(?<to>\d+)/i', $m, $matches) )
			{
				$from = $matches['from'];
				$to = $matches['to'];
		
				// If we found migration method from current db version
				if( $from == $version )
				{
					elapsed('Databse migration from version: '.$from.' -> '.$to);
						
					// Run migration method
					if( call_user_func( array($version_handler[0], $m)) !== false )
					{
						// Save current version for further migrating
						$version = $to;
						
						// Call database version changing handler
						call_user_func( $version_handler, $to );		
					}
					// Break and error
					else
					{
						e('Database migration from ## -> ## - has Failed', E_SAMSON_ACTIVERECORD_ERROR, array( $from, $to));
						break;
					}
				}
			}
		}
	}
	
	/** @see idb::profiler() */
	public function profiler()
	{					
		// Выведем список объектов из БД
		$list = array();
		
		// Общее кво созданных объектов
		$total_obj_count = 0;
		
		// Переберм коллекции созданных объектов 
		foreach ( dbRecord::$instances as $n => $v ) 
		{ 
			// Если для данного класса были созданы объекты
			if( $c = sizeof($v) )
			{ 
				// Увеличим общий счетчик созданных объектов
				$total_obj_count += $c;			
				
				// Выведем имя класса и кво созданных объектов
				$list[] = ''.$n.'('.$c.')'; 
			}
		}
		
		// Сформируем строку профайлинга
		return 'DB: '.round($this->elapsed,3).'с, '.$this->query_count.' запр., '.$total_obj_count.' об.('.implode($list,',').')';
	}
	
	//
	// Приватный контекст
	//
	
	/**
	 * Create SQL request 
	 * 
	 * @param string $class_name Classname for request creating
	 * @param dbQuery $query Query with parameters
	 * @return string SQL string
	 */
	private function prepareSQL( $class_name, dbQuery $query )
	{
		//elapsed( 'dbMySQL::find() Начало');
		$params = $this->__get_table_data( $class_name );
		// Получим переменные для запроса
		extract($params);
			
		// Текст выборки полей
		$select = '`'.$_table_name.'`.*';//$_sql_select['this'];
		
		// If virtual fields defined
		if( sizeof( $query->virtual_fields ) ) $select .= ', '."\n".implode("\n".', ', $query->virtual_fields);
		
		$from = ' ( '.$this->prepareInnerSQL( $class_name, $query, $params );
		
		// Добавим алиас
		$from .= ' ) as `'.$_table_name.'`';
		
		//trace($query->join);
		
		// Iterate related tables
		foreach ($query->join as $relation_data )
		{
			$c_table = self::$prefix.$relation_data->table;
			
			// Если существует требуемая связь
			if( isset( $_sql_from[ $c_table ] ) )
			{
				// Получим текст для выборки данных из связанных таблиц
				$select .= ','.$_sql_select[ $c_table ];
		
				// Получим текст для привязывания таблицы к запросу
				$from .= "\n".' '.$_sql_from[ $c_table ];
			}
			else return e('Ошибка! В таблице связей для класса(##), не указана связь с классом(##)',E_SAMSON_FATAL_ERROR,array( $class_name, $c_table));
		}
		
		// Сформируем строку запроса на поиск записи
		$sql = "\n".'SELECT '.$select."\n".' FROM '.$from;
		
		// Получим все условия запроса
		$sql .= "\n".' WHERE ('.$this->getConditions( $query->condition, $class_name ).')';
		
		// Добавим нужные сортировщики
		if( sizeof( $query->group )) $sql .= "\n".' GROUP BY '.$query->group[0];
		// Если указана сортировка результатов
		if( sizeof( $query->order )) $sql .= "\n".' ORDER BY '.$query->order[0].' '.$query->order[1];
		// Если нужно ограничить к-во записей в выдаче по главной таблице
		if( sizeof( $query->limit )) $sql .= "\n".' LIMIT '.$query->limit[0].(isset($query->limit[1])?','.$query->limit[1]:'');
		
		if( isset($GLOBALS['show_sql']) ) elapsed( $sql);
		
		return $sql;
	}
	
	private function prepareInnerSQL( $class_name, dbQuery $query, $params )
	{
		//trace($class_name);
		//print_r($query->own_condition);
		// Получим текст цели запроса
		$from = 'SELECT '.$params['_sql_select']['this'];
		
		// Если заданны виртуальные поля, добавим для них колонки
		if( sizeof( $query->own_virtual_fields ) ) $from .= ', '."\n".implode("\n".', ', $query->own_virtual_fields);
		
		// From part
		$from .="\n".' FROM '.$params['_sql_from']['this'];
		
		// Если существуют условия для главной таблицы в запросе - получим их
		if( sizeof( $query->own_condition->arguments )) $from .= "\n".' WHERE ('.$this->getConditions($query->own_condition, $class_name).')';
		
		// Добавим нужные групировщики
		$query->own_group = array_merge( $params['_own_group'], is_array($query->own_group) ? $query->own_group : array() );
		if( sizeof( $query->own_group )) $from .= "\n".'GROUP BY '.implode(',', $query->own_group);
		// Если указана сортировка результатов
		if( sizeof( $query->own_order )) $from .= "\n".' ORDER BY '.$query->own_order[0].' '.$query->own_order[1];
		// Если нужно ограничить к-во записей в выдаче по главной таблице
		if( sizeof( $query->own_limit )) $from .= "\n".' LIMIT '.$query->own_limit[0].(isset($query->own_limit[1])?','.$query->own_limit[1]:'');
		
		return $from;		
	}
	
	private function getConditions(Condition $cond_group, $class_name )
	{
		// Соберем сюда все сформированные условия для удобной "упаковки" их в строку
		$sql_condition = array();
			
		// Переберем все аргументы условий в условной группе условия
		foreach ($cond_group->arguments as $argument){
			// Если аргумент я вляется группой аргументов, разпарсим его дополнительно
			if (is_a($argument, ns_classname('Condition','samson\activerecord'))) {
				$sql_condition[] = $this->getConditions( $argument, $class_name);
			} else {
				// Если условие успешно разпознано - добавим его в коллекцию условий
				$sql_condition[] = $this->parseCondition( $class_name, $argument );
			}
		}
		
		// Соберем все условия условной группы в строку
		if(sizeof($sql_condition)) return '('.implode( ') '.$cond_group->relation.' (', $sql_condition ).')';
		// Вернем то что получилось
		else return '(1=1)';
	}
	
	/**
	 * "Правильно" разпознать переданный аргумент условия запроса к БД
	 *
	 * @param string 				$class_name	Схема сущности БД для которой данные условия
	 * @param dbConditionArgument 	$arg 		Аругемнт условия для преобразования
	 * @return string Возвращает разпознанную строку с условием для MySQL
	 */
	private function parseCondition( $class_name, Argument & $arg )
	{
		// Получим переменные для запроса
		extract($this->__get_table_data( $class_name ));
	
		// Получим "правильное" имя аттрибута сущности и выделим постоянную часть условия
		$sql_cond_t = isset( $_map[ $arg->field ] ) ? $_map[ $arg->field ] : $arg->field;
	
		// Если аргумент условия - это НЕ массив - оптимизации по более частому условию
		if( !is_array( $arg->value ) ) 
		{
			// NULL condition
			if( $arg->relation === dbRelation::NOTNULL || $arg->relation === dbRelation::ISNULL ) return $sql_cond_t.$arg->relation;
			// Own condition
			else if( $arg->relation === dbRelation::OWN) return $arg->field;
			// Regular condition
			else return $sql_cond_t.$arg->relation.$this->protectQueryValue( $arg->value);
		}
		// Если аргумент условия - это массив и в нем есть значения
		else if( sizeof( $arg->value ))
		{
			switch( $arg->relation )
			{
				case dbRelation::EQUAL: return $sql_cond_t.' IN ("'.implode( '","', $arg->value ).'")';
				case dbRelation::NOT_EQUAL: return $sql_cond_t.' NOT IN ("'.implode( '","', $arg->value ).'")';
			}
		}
	}
	
	/**
	 * Получить "правильную" коллекцию полей для формирования запроса к БД
	 * 
	 * @param string 	$class_name 	Имя класса
	 * @param idbRecord $object 		Объект для которого формируется список полей
	 * @param boolean 	$straight 		Флаг вывода массива вида: КЛЮЧ = ЗНАЧЕНИЕ
	 */
	private function & getQueryFields( $class_name, idbRecord & $object = NULL, $straight = FALSE )
	{		
		// Получим переменные для запроса
		extract($this->__get_table_data( $class_name ));	
		
		// Результирующая коллекция
		$collection = array();

		// Установим флаг получения значений атрибутов из переданного объекта
		$use_values = isset($object);
		
		// Получим имя таблицы где хранится сущность
		$table = $_table_name;		

		// Переберем "настоящее" имена атрибутов схемы данных для объекта
		foreach ( $_table_attributes as $attribute => $map_attribute )
		{		
			// Отметки времени не заполняем
			if( $_types[ $attribute ] == 'timestamp' ) continue;
			
			// Основной ключ не заполняем
			if( $_primary == $attribute ) continue;	

			// Получим значение атрибута объекта защитив от инъекций, если объект передан
			$value = $use_values ? $this->protectQueryValue( $object->$map_attribute ) : '';
		
			// Добавим значение поля, в зависимости от вида вывывода метода 
			$collection[ $map_attribute ] = ($straight ? $table.'.'.$map_attribute.'=':'').$value;
		}	
		
		// Если схема данных работает через "Маппинг" подставим имя сущности для запроса
		// только в том случаи, если это не прямой запрос к общей таблице "Маппинга"
		//if( $scheme->has('Entity') && ($scheme->entity_name != 'scmstable') ) 
		//	$collection['Entity'] = ($straight ? $table.'.Entity'.'=':'').'"'.$scheme->entity_name.'"'; 
		
		// Вернем полученную коллекцию
		return $collection;		
	}
	
	/**
	 * Выполнить защиту значения поля для его безопасного использования в запросах
	 * 
	 * @param string $value Значения поля для запроса
	 * @return string $value Безопасное представление значения поля для запроса
	 */
	private function protectQueryValue( $value )
	{
		// If magic quotes are on - remove slashes
		if( get_magic_quotes_gpc() ) $value = stripslashes( $value ); 
				
		// Normally escape string
		$value = mysql_real_escape_string( $value );
			
		// Return value in quotes for query
		return '"'.$value.'"';
	}

    /**
     * Create object instance by specified parameters
     * @param string       $className   Object class name
     * @param RelationData $metaData    Object metadata for creation and filling
     * @param array        $dbData      Database record with object data
     *
     * @return idbRecord Database record object instance
     */
    private function & createObject($className, $identifier, array & $attributes, array & $dbData, array & $virtualFields = array())
    {
        // If this object instance is not cached
        if(!isset(dbRecord::$instances[ $className ][ $identifier ]) || isset($dbData['__Count']) || sizeof($virtualFields)) {

            // Create empry dbRecord ancestor and store it to cache
            dbRecord::$instances[ $className ][ $identifier ] = new $className(false);

            // Pointer to object
            $object = & dbRecord::$instances[ $className ][ $identifier ];

            // Set object identifier
            $object->id = $identifier;

            // Fix object connection with DB record
            $object->attached = true;

            // Fill object attributes
            foreach ($attributes as $lc_field => $field) {
                $object->$lc_field = $dbData[ $field ];
            }

            // Fill virtual fields
            foreach ($virtualFields as $alias => $virtual_field) {
                // If DB record contains virtual field data
                if (isset($dbData[ $alias ])) {
                    $object->$alias = $dbData[ $alias ];
                }
            }

            return $object;

        } else { // Get object instance from cache
            return dbRecord::$instances[ $className ][ $identifier ];
        }
    }
	
	/**
	 * Преобразовать массив записей из БД во внутреннее представление dbRecord
	 * @param string	$class_name	Имя класса
	 * @param array		$response	Массив записей полученных из БД
	 * @return array Коллекцию записей БД во внутреннем формате
	 * @see dbRecord
	 */
	private function & toRecords( $class_name, array & $response, array $join = array(), array $virtual_fields = array() )
	{
        //[PHPCOMPRESSOR(remove,start)]
        s()->benchmark( __FUNCTION__, func_get_args(), __CLASS__ );
        //[PHPCOMPRESSOR(remove,end)]

		// Сформируем правильное имя класса		
		$class_name = ns_classname( $class_name, 'samson\activerecord');
		
		// Результирующая коллекция полученных записей из БД
		$collection = array();

		// Получим переменные для запроса
		extract($this->__get_table_data($class_name));

        // Generate table metadata for joined tables
        $joinedTableData = array();
        foreach ($join as $relationData) {

            // Generate full joined table name(including prefix)
            $joinTable = self::$prefix.$relationData->table;

            // Get real classname of the table without alias
            $tableName = $_relation_alias[ $joinTable ];

            // Get joined table class metadata
            $joinedTableData[$tableName] = $this->__get_table_data($tableName);
        }

		// Получим имя главного
		$main_primary = $_primary;

		// Перебем массив полученных данных от БД - создадим для них объекты
		$records_count = sizeof( $response );

		// Идентификатор текущего создаваемого объекта
		$main_id = isset($response[ 0 ]) ? $response[ 0 ][ $main_primary ] : 0;
		
		// Указатель на текущий обрабатываемый объект
		$main_obj = null;
		
		// Переберем полученные записи из БД
		for ( $i = 0; $i < $records_count; $i++ ) 
		{
			// Строка данных полученная из БД 
			$db_row = & $response[ $i ];	

            // Get object instance
            $collection[ $main_id ] = & $this->createObject($class_name, $main_id, $_attributes, $db_row, $virtual_fields);

            // Pointer to main object
            $main_obj = & $collection[ $main_id ];
			
			// Выполним внутренний перебор строк из БД начиная с текущей строки
			// Это позволит нам розабрать объекты полученные со связью один ко многим
			// А если это связь 1-1 то цикл выполниться только один раз
			for ($j = $i; $j < $records_count; $j++) {
				// Строка данных полученная из БД
				$db_inner_row = & $response[ $j ];
				
				// Получим идентфиикатор главного объекта в текущей строче БД
				$obj_id = $db_inner_row[ $main_primary ];				
				
				// Если в строке из БД новый идентификатор
				if ($obj_id != $main_id) {
					// Установим новый текущий идентификатор материала
					$main_id = $obj_id;
						
					// Установим индекс главного цикла на строку с новым главным элементом
					// учтем что главный цикл сам увеличит на единицу индекс
					$i = $j - 1;

					//trace(' - Найден новый объект на строке №'.$j.'-'.$db_inner_row[$main_primary]);
						
					// Прервем внутренний цикл
					break;
				}
				//else trace(' + Заполняем данные из строки №'.$j);			
				
				// Переберем все присоединенные таблицы в запросе
				foreach ($join as $relation_data) {
                    /**@var \samson\activerecord\RelationData $relation_data*/

                    // If this table is not ignored
                    if (!$relation_data->ignore) {

                        // TODO: Prepare all data in RelationObject to speed up this method

                        $join_name = $relation_data->relation;

                        $join_table = self::$prefix.$relation_data->table;

                        //trace('Filling related table:'.$join_name.'/'.$join_table);

                        // Get real classname of the table without alias
                        $_relation_name = $_relation_alias[ $join_table ];
                        $join_class = str_replace( self::$prefix, '', $relation_data->table);

                        // Get joined table metadata from previously prepared object
                        $r_data = $joinedTableData[ $_relation_name ];

                        // Try to get identifier
                        if( isset($_relations[ $join_table ][ $r_data['_primary'] ]) ) $r_obj_id_field = $_relations[ $join_table ][ $r_data['_primary'] ];
                        // Получим имя ключевого поля связанного объекта
                        else e('Cannot find related table(##) primary field(##) description', E_SAMSON_ACTIVERECORD_ERROR, array($join_table, $r_data['_primary']) );

                        // Если задано имя ключевого поля связанного объекта - создадим его
                        if( isset( $db_inner_row[ $r_obj_id_field ] ))
                        {
                            // Получим ключевое поле связанного объекта
                            $r_obj_id = $db_inner_row[ $r_obj_id_field ];

                            // Get joined object instance
                            $r_obj = & $this->createObject($join_name, $r_obj_id, $_relations[ $join_table ], $db_inner_row);

                            // Call handler for object filling
                            $r_obj->filled();

                            // TODO: Это старый подход - сохранять не зависимо от алиаса под реальным именем таблицы

                            // Если связанный объект привязан как один-к-одному - просто довами ссылку на него
                            if( $_relation_type[ $join_table ] == 0 )
                            {
                                $main_obj->onetoone[ '_'.$join_table ] = $r_obj;
                                $main_obj->onetoone[ '_'.$join_class ] = $r_obj;
                            }
                            // Иначе создадим массив типа: идентификатор -> объект
                            else
                            {
                                $main_obj->onetomany[ '_'.$join_table ][ $r_obj_id ] = $r_obj;
                                $main_obj->onetomany[ '_'.$join_class ][ $r_obj_id ] = $r_obj;
                            }
                        }
                    }
				}				
			}
			
			// Call handler for object filling
			$main_obj->filled();

			// Если внутренний цикл дошел до конца остановим главный цикл
			if( $j == $records_count ) break;
		}

		// Вернем то что у нас вышло
		return $collection;
	}
	
	/**
	 * Get closes class to dbRecord in class ierarchy
	 * @param string $class_name Class for analyzing
	 * @return string Closes class to dbRecord
	 */
	private function getClassName( $class_name )
	{			 
		return ($parentclass = get_parent_class( $class_name )) == ns_classname( 'dbRecord', 'samson\activerecord') ? $class_name : $parentclass; 
	}
} 
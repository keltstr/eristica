<?php 
namespace samson\activerecord;

/**
 * Аргумент условия запроса к БД
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @author Nikita Kotenko <nick.w2r@gmail.com>
 * @deprecated use Argument
 */
class dbConditionArgument extends Argument
{	
	/**
	 * Конструктор
	 * 
	 * @param string $field Имя поля БД к которому относится данный аргумент
	 * @param string $value Значение аргумента в условии запроса
	 */
	public function __construct( $field, $value, $relation = NULL )
	{		
		parent::__construct( $field, $value, $relation );	
		
		// Попытаемя розпарсить отношение из поля условия
		if( preg_match('/_(?<relation>gte|lte|gt|eq|ne|lt|like)_?/iu', $field, $relation_match ) )
		{	
			// Удалим отношение из имени поля условия
			$this->field = str_replace( $relation_match[0], '', $field );
			
			// Определим отношение между полем и аргументов
			switch ($relation_match['relation'])
			{
				case '>'	:
				case 'gt'	: $this->relation = dbRelation::GREATER;		break;
				
				case '>='	:
				case 'gte'	: $this->relation = dbRelation::GREATER_EQ;		break;
				
				case '<'	:
				case 'lt'	: $this->relation = dbRelation::LOWER;			break;
				
				case '<='	:
				case 'lte'	: $this->relation = dbRelation::LOWER_EQ;		break;
				
				case '!='	:
				case 'ne'	: $this->relation = dbRelation::NOT_EQUAL;		break;
				
				case '='	:
				case 'eq'	:
					
				case 'like'	: $this->relation = dbRelation::LIKE;			break;
				
				default		: $this->relation = dbRelation::EQUAL;
			}	
		}
	}
}
?>
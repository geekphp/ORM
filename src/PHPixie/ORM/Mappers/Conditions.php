<?php

namespace PHPixie\ORM\Mappers;

class Conditions
{
    protected $models;
    protected $relationships;
    protected $planners;
    
    protected $databaseModel;
    protected $relationshipMap;

    public function __construct($models, $relationships, $planners)
    {
        $this->models = $models;
        $this->relationships = $relationships;
        $this->planners = $planners;
        
        $this->databaseModel   = $models->database();
        $this->relationshipMap = $relationships->map();
    }
    
    protected function mapOperatorCondition($builder, $condition)
    {
        $builder->addOperatorCondition(
            $condition->logic(),
            $condition->isNegated(),
            $condition->field(),
            $condition->operator(),
            $condition->values()
        );
    }
    
    protected function mapConditionGroup($builder, $modelName, $group, $plan)
    {
        $builder->startGroup($group->logic(), $group->isNegated());
        $this->mapConditions($builder, $modelName, $group->conditions(), $plan);
        $builder->endGroup();
    }
    
    public function mapEmbeddedCollection($builder, $modelName, $embeddedCollection, $plan)
    {
        
    }
    
    protected function mapRelationshipGroup($builder, $modelName, $group, $plan)
    {
        $side = $this->relationshipMap->getSide($modelName, $group->relationship());
        $type = $side->relationshipType();
        $handler = $this->relationships->get($type)->handler();
        
        if($builder instanceof \PHPixie\Database\Query) {
            $handler->mapDatabaseQuery($builder, $side, $group, $plan);
        }else{
            $handler->mapEmbeddedContainer($builder, $side, $group, $plan);
        }
    }
    
    protected function mapDatabaseQuery($builder, $modelName, $query, $plan)
    {
        $builder->startGroup('or', false);
        $this->mapConditions($builder, $modelName, $query->conditions(), $plan);
        $builder->endGroup();
    }
    
    protected function addInAllCondition($builder, $modelName, $logic, $negate)
    {
        $builder->addInOperatorCondition(
            $idField,
            array(),
            $logic,
            !$negate
        );
    }
    
    protected function mapInCondition($builder, $modelName, $inCondition, $plan)
    {
        if(!($builder instanceof \PHPixie\Database\Query))
            throw new \PHPixie\ORM\Exception\Mapper("Collection conditions are not allowed for embedded models");
        
        
        $logic  = $inCondition->logic();
        $negate = $inCondition->isNegated();
        $items  = $inCondition->items();
        
        if($items === null) {
            $this->addInAllCondition($builder, $modelName, $logic, $negate);
            
        }elseif($items === array()) {
            $this->addInAllCondition($builder, $modelName, $logic, !$negate);
            
        }else{
            
            $builder->startConditionGroup($inCondition->logic(), $inCondition->isNegated());
            $ids = array();
            if($item instanceof \PHPixie\ORM\Models\Type\Database\Query) {
                $this->mapDatabaseQuery($builder, $modelName, $item, $plan);
                
            }else{
                $ids[]=$item;
                
            }
        }
        
        
        
        
        
        $ids = array();
        
        foreach( as $item) {
            if($item instanceof \PHPixie\ORM\Models\Type\Database\Query) {
                $this->mapDatabaseQuery($builder, $modelName, $item, $plan);
                
            }else{
                $ids[]=$item;
                
            }
        }
        
        if(!empty($ids) || empty($items)) {
            $idField = $this->databaseModel->config($modelName)->idField;
            $builder->addInOperatorCondition($idField, $ids, 'or', false);
        }
        
        
        $this->endGroup();
        /*
        $collection = $this->planners->collection($modelName, $collectionCondition->items());
        
        
        $this->planners->in()->collection(
            $builder,
            $idField,
            $collection,
            $idField,
            $plan,
            $collectionCondition->logic(),
            $collectionCondition->isNegated()
        );
        */
    }
    
    public function map($builder, $modelName, $conditions, $plan)
    {
        foreach ($conditions as $condition) {
            
            if ($condition instanceof \PHPixie\ORM\Conditions\Condition\Operator) {
                $this->mapOperatorCondition($builder, $condition);
                
            }elseif ($condition instanceof \PHPixie\ORM\Conditions\Condition\In) {
                $this->mapInCondition($builder, $modelName, $condition, $plan);

            }elseif ($condition instanceof \PHPixie\ORM\Conditions\Condition\Group\Relationship) {
                $this->mapRelationshipGroup($builder, $modelName, $condition, $plan);

            }elseif ($condition instanceof \PHPixie\ORM\Conditions\Condition\Group) {
                $this->mapConditionGroup($builder, $modelName, $condition, $plan);

            }else {
                throw new \PHPixie\ORM\Exception\Mapper("Unexpected condition encountered");
            }
        }
    }
}

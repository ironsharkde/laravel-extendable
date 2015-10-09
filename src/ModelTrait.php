<?php
/**
 * Created by PhpStorm.
 * User: antonpauli
 * Date: 24/07/15
 * Time: 13:19
 */

namespace IronShark\Extendable;

use IronShark\Extendable\CustomFieldConfigProvider;


trait ModelTrait
{
    public $customAttributes = [];

    /**
     * Boot trait
     */
    public static function bootModelTrait()
    {
        static::creating(function($item){
            foreach($item->customFieldNames() as $name){
            }
        });
    }


    /**
     * Begin querying a model with eager loading.
     *
     * @param  array|string  $relations
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function withCustomFields($relations = null)
    {
        $instance = new static;

        if($relations === null)
            $relations = CustomFieldConfigProvider::fieldNames(get_class($instance));

        if (is_string($relations)) {
            $relations = func_get_args();
        }

        return $instance->newQuery()->with($relations);
    }


    /**
     * Request custom field relations, or call model methods.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if($this->isCustomField($name))
            return $this->customFieldRelation($name);

        $query = $this->newQuery();
        return call_user_func_array(array($query, $name), $arguments);
    }


    /**
     * Return custom field relation for specified field name.
     *
     * @param $fieldName
     * @return mixed
     */
    public function customFieldRelation($fieldName){
        return $this->morphOne('IronShark\Extendable\CustomField', 'parent', 'parent_type')
            ->where('field_name', $fieldName);
    }


    /**
     * Return all custom field names for current model
     *
     * @return array
     */
    public function customFieldNames(){
        return CustomFieldConfigProvider::fieldNames(get_class($this));
    }


    /**
     * Return true if attribute name belongs to fields.
     *
     * @param $attributeName
     * @return bool
     */
    public function isCustomField($attributeName){
        return in_array($attributeName, $this->customFieldNames());
    }


    /**
     * Dynamically retrieve attributes on the model or custom fields.
     *
     * @param $name
     * @return mixed
     */
    public function __get($name) {
        // return custom field value
        if($this->isCustomField($name))
            return $this->getCustomFieldModel($name)->value;

        // return model attribute
        return $this->getAttribute($name);
    }


    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        // set
        if($this->isCustomField($key)){
            if($value instanceof CustomField)
                $this->$key = $value;
            else
                $this->customAttributes[$key] = $value;
        } else {
            parent::__set($key, $value);
        }

    }


    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array()){

        parent::save($options);

        // save custom fields
        foreach($this->customFieldNames() as $name){
            // custom field model instance
            $customFieldModel = $this->getCustomFieldModel($name);
            $customFieldModel->value = isset($this->customAttributes[$name]) ? $this->customAttributes[$name] : null;
            $customFieldModel->save();
        }
    }


    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes) {
        $this->fillCustomAttributes($attributes);
        parent::fill($attributes);
    }


    /**
     * Fill custom fields
     *
     * @param array $attributes
     */
    public function fillCustomAttributes(array $attributes){
        foreach($this->customFieldNames() as $name) {

            if(isset($attributes[$name]))
                $this->customAttributes[$name] = $attributes[$name];
        }
    }


    /**
     * Delete the model from the database.
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete(){

        // delete model
        $parentResult = parent::delete();

        // delete custom fields
        if($parentResult) {
            CustomField::where([
                'parent_type' => get_class($this),
                'parent_id' => $this->id
            ])->delete();
        }

        return $parentResult;
    }


    /**
     * Returns custom field model instance
     *
     * @param $key
     * @return mixed
     */
    public function customFieldModel($key) {
        return $this->relations[$key];
    }


    /**
     * Create new custom field model instance
     *
     * @param $fieldName
     * @return CustomField
     */
    public function newCustomFieldModel($fieldName){
        return new CustomField([
            'field_name' => $fieldName,
            'parent_type' => get_class($this),
            'parent_id' => $this->id
        ]);
    }


    /**
     * Returns custom field model
     *
     * @param $fieldName
     * @return CustomField
     */
    public function getCustomFieldModel($fieldName){

        $model = $this->getAttribute($fieldName);

        if($model === null){
            $model = $this->newCustomFieldModel($fieldName);
            //$this->$fieldName = $model;
        }

        return $model;
    }


    /**
     * Loads custom field model
     *
     * @param $fieldName
     * @return mixed
     */
    public function getAttribute($fieldName) {
        $model = parent::getAttribute($fieldName);

        if($model === null && $this->exists) {
            $model = CustomField::where([
                'parent_type' => get_class($this),
                'parent_id' => $this->id,
                'field_name' => $fieldName
            ])->first();
        }

        return $model;
    }
}
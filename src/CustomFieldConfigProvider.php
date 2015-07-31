<?php
/**
 * Created by PhpStorm.
 * User: antonpauli
 * Date: 27/07/15
 * Time: 18:35
 */

namespace IronShark\Extendable;

use IronShark\Extendable\CustomFieldType;

class CustomFieldConfigProvider
{
    /**
     * Return config customfield configs for given model
     *
     * @param $modelClass
     * @return mixed
     */
    public static function customFieldConfigs($modelClass){
        $customFieldsConfig = config('custom-fields');
        return array_get($customFieldsConfig, $modelClass);
    }


    /**
     * Return custom field model field name for specified model and fieldtype
     *
     * @param $modelClass
     * @param $fieldName
     * @return string
     */
    public static function fieldType($modelClass, $fieldName){
        $fieldConfigs = self::customFieldConfigs($modelClass);

        switch ($fieldConfigs[$fieldName]['type']) {
            case CustomFieldType::Checkbox:
            case CustomFieldType::Select:
            case CustomFieldType::String:
            case CustomFieldType::Radio:
                return 'stringvalue';
            case CustomFieldType::Text:
                return 'textvalue';
            case CustomFieldType::DateTime:
                return 'datetime';
            default:
                return 'stringvalue';
        }
    }


    /**
     * Return all custom field names for specified model
     *
     * @param $modelClass
     * @return array
     */
    public static function fieldNames($modelClass) {
        $customFieldsConfig = config('custom-fields');
        return array_keys(array_get($customFieldsConfig, $modelClass, []));
    }
}
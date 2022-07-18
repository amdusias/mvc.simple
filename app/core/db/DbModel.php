<?php

namespace app\core\db;

use app\core\Application;
use app\core\Model;

/**
 * Class DbModel
 */
abstract class DbModel extends Model
{
    abstract public static function tableName(): string;

    public static function primaryKey(): string
    {
        return 'id';
    }

    /**
     * Return query
     * @param $sql
     * @param array $params
     * @return bool
     */
    public static function query($sql, array $params = []): bool
    {
        return Application::$app->db->query($sql, $params);
    }

    /**
     * Return row
     * @param $sql
     * @param array $params
     * @return array
     */
    public static function getRow($sql, array $params = []): array
    {
        return Application::$app->db->getRow($sql, $params);
    }

    /**
     * Save in table
     * @return bool
     */
    public function save(): bool
    {
        $tableName = $this->tableName();

        $setAttributes = [];
        $valueAttributes = [];
        $attributes = $this->attributes();

        foreach ($attributes as $attribute) {
            $setAttributes[] = "`{$attribute}` = ?";
            $valueAttributes[] = $this->{$attribute};
        }

        $sql = "INSERT INTO `{$tableName}` SET ".(implode(",", $setAttributes))." ";
        return self::query($sql, $valueAttributes);
    }

    /**
     * Find row in table
     * @param $where
     * @return array
     */
    public static function findOne($where): array
    {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode("AND", array_map(fn($attr) => "`{$attr}` = :$attr", $attributes));
        return self::getRow("SELECT * FROM `{$tableName}` WHERE $sql", $where);
    }
}
<?php

namespace Osquatro\example;


class DBAdapterImpl implements \Osquatro\cards\IDBAdapter
{
    const TABLE_NAME = 'cards_strategy';
    const ID_FIELD = 'id';
    const NAME_FIELD = 'name';
    const CONFIGURATION_FIELD = 'configuration';
    const FILE_FIELD = 'file';

    const SELECT_TPL = "SELECT %s FROM %s";
    const SELECT_WHERE_TPL = "SELECT %s FROM %s WHERE %s";

    var $db;
    var $prefix;
    var $tableName;

    function __construct($db, $prefix='') {
        $this->db = $db;
        $this->prefix = $prefix;
        $this->tableName = sprintf("`%s%s`", $prefix, static::TABLE_NAME);
    }

    function getAllConfigurations($limit=false, $offset=false, $search=false, $orderby=false, $order=false)
    {

        $query = sprintf(static::SELECT_TPL,
            implode(",", array(static::ID_FIELD, static::NAME_FIELD, static::FILE_FIELD)),
            $this->tableName);

        return $this->db->select($query, array(), array());
    }

    function  getConfigurations(array $ids)
    {
        $idsList = implode(",", $ids);
        $where = sprintf("%s IN (%s) ORDER BY FIELD(%s, %s)", static::ID_FIELD, $idsList, static::ID_FIELD, $idsList);

        $query = sprintf(static::SELECT_WHERE_TPL,
            implode(",", array(static::ID_FIELD, static::NAME_FIELD, static::CONFIGURATION_FIELD, static::FILE_FIELD)),
            $this->tableName,
            $where);
        return $this->db->select($query, array(), array());
    }

    function getConfigurationById($id)
    {
        $where = sprintf("%s=?", static::ID_FIELD);
        $query = sprintf(static::SELECT_WHERE_TPL,
            implode(",", array(static::ID_FIELD, static::NAME_FIELD, static::CONFIGURATION_FIELD, static::FILE_FIELD)),
            $this->tableName,
            $where);
        $results = $this->db->select($query, array($id), array('%d'));
        if (count($results) > 0) {
            return $results[0];
        }

        return false;
    }

    function  saveConfiguration($name, $configuration)
    {
        $data = array(static::NAME_FIELD => $name, static::CONFIGURATION_FIELD => $configuration);
        $format = array('%s', '%s');

        return $this->db->insert($this->tableName, $data, $format);
    }

    function removeConfigurationById($id)
    {
        return $this->db->delete($this->tableName, $id);
    }

    function updateConfigurationById($id, $name, $configuration=false, $file=false)
    {
        $format = array();
        $data = [];

        $map = array(
            static::NAME_FIELD => $name,
            static::CONFIGURATION_FIELD => $configuration,
            static::FILE_FIELD => $file);

        foreach ($map as $key => $value ) {
            if (is_string($value)) {
                $data[$key] = $value;
                $format []= '%s';
            }
        }

        return $this->db->update($this->tableName, $data, $format, array(static::ID_FIELD => $id), array('%d'));
    }

    function getConfigurationField()
    {
        return static::CONFIGURATION_FIELD;
    }

    function getNameField()
    {
        return static::NAME_FIELD;
    }

    function getIdField()
    {
        return static::ID_FIELD;
    }

    function getFileField()
    {
        return static::FILE_FIELD;
    }
}
<?php

namespace Osquatro\cards;


interface IDBAdapter
{
    function getAllConfigurations($limit=false, $offset=false, $search=false, $orderby=false, $order=false);

    function getConfigurations(array $ids);

    function getConfigurationById($id);

    function saveConfiguration($name, $configuration);

    function removeConfigurationById($id);

    function updateConfigurationById($id, $name, $configuration, $file);

    function getConfigurationField();

    function getNameField();

    function getIdField();

    function getFileField();

}
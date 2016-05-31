<?php

namespace Osquatro\cards;

class CommandProcessor {

    const GET_ALL_CONFIGURATIONS_COMMAND = 'get_all_configurations';
    const GET_CONFIGURATION_BY_ID_COMMAND = 'get_configuration_by_id';
    const REMOVE_CONFIGURATION_BY_ID_COMMAND = 'remove_configuration_by_id';
    const UPDATE_CONFIGURATION_BY_ID_COMMAND = 'update_configuration_by_id';
    const COPY_CONFIGURATION_BY_ID_COMMAND = 'copy_configuration_by_id';
    const SAVE_CONFIGURATION_COMMAND = 'save_configuration';
    const EXPORT_CONFIGURATION_TO_PNG_BY_ID_COMMAND = 'export_configuration_to_png_by_id';
    const EXPORT_CONFIGURATION_TO_GIF_BY_ID_COMMAND = 'export_configuration_to_gif_by_id';

    const SUCCESS_KEY = 'success';
    const ERRORS_KEY = 'errors';

    var $exporter;
    var $dbAdapter;

    var $NAME_INDEX;
    var $CONFIGURATION_INDEX;
    var $FILE_INDEX;
    var $ID_INDEX;
    var $exportPath;
    var $imagePath;
    var $deleteFilesOnUpdate;

    public function __construct(IDBAdapter $dbAdapter, $imagePath, $exportPath, $deleteFilesOnUpdate=false) {
        $this->dbAdapter = $dbAdapter;

        $this->ID_INDEX = $dbAdapter->getIdField();
        $this->CONFIGURATION_INDEX = $dbAdapter->getConfigurationField();
        $this->NAME_INDEX = $dbAdapter->getNameField();
        $this->FILE_INDEX = $dbAdapter->getFileField();

        $this->exportPath = $exportPath;
        $this->imagePath = $imagePath;
        $this->deleteFilesOnUpdate = $deleteFilesOnUpdate;
    }


    public function processCommand($input) {
        $command = isset($input['command']) ? $input['command'] : 'unknown';
        $result = new \stdClass();
        switch($command) {
            case self::GET_ALL_CONFIGURATIONS_COMMAND :
                $this->getAllConfigurations($input, $result);
                break;
            case self::SAVE_CONFIGURATION_COMMAND :
                $this->saveConfiguration($input, $result);
                break;
            case self::GET_CONFIGURATION_BY_ID_COMMAND :
                $this->getConfigurationById($input, $result);
                break;
            case self::REMOVE_CONFIGURATION_BY_ID_COMMAND :
                $this->removeById($input, $result);
                break;
            case self::UPDATE_CONFIGURATION_BY_ID_COMMAND :
                $this->updateById($input, $result);
                break;
            case self::COPY_CONFIGURATION_BY_ID_COMMAND :
                $this->copyConfigurationById($input, $result);
                break;
            case self::EXPORT_CONFIGURATION_TO_PNG_BY_ID_COMMAND :
                $this->exportConfigurationToPngById($input, $result);
                break;
            case self::EXPORT_CONFIGURATION_TO_GIF_BY_ID_COMMAND :
                $this->exportConfigurationToGifById($input, $result);
                break;
            default:
                $result->{self::SUCCESS_KEY} = false;
                $result->{self::ERRORS_KEY} = array( 'Command not found' );
                break;
        }

        return $result;
    }

    private function getAllConfigurations($input, $result) {
        $limit = isset($input['limit']) ? $input['limit'] : false;
        $offset = isset($input['offset']) ? $input['offset'] : false;
        $search = isset($input['search']) ? $input['search'] : false;
        $orderby = isset($input['orderby']) ? $input['orderby'] : false;
        $order = isset($input['order']) ? $input['order'] : false;

        $results = $this->dbAdapter->getAllConfigurations($limit, $offset, $search, $orderby, $order);
        $result->{'configurations'} = $results;
        $result->{self::SUCCESS_KEY} = true;
    }

    private function saveConfiguration($input, $result) {
        $success = false;
        $validation = array('name' => 'string', 'configuration' => 'json');

        if ($this->checkRequest($input, $validation, $result)) {
            $name = $input['name'];
            $conf = $input['configuration'];

            $insertResult = $this->dbAdapter->saveConfiguration($name, $conf, '');
            if ($insertResult) {
                $success = true;
                $result->{'id'} = $insertResult;
            }

        }

        $result->{self::SUCCESS_KEY} = $success;
    }

    private function copyConfigurationById($input, $result) {
        $success = false;
        $validation = array('id' => 'int');

        if ($this->checkRequest($input, $validation, $result)) {
            $id = $input['id'];

            $conf = $this->dbAdapter->getConfigurationById($id);
            if (isset($input['name']))
                $name = $input['name'];
            else
                $name = $conf[$this->NAME_INDEX].'-copy';

            $conf = $conf[$this->CONFIGURATION_INDEX];

            $insertResult = $this->dbAdapter->saveConfiguration($name, $conf);

            if ($insertResult) {
                $result->{'id'} = $insertResult;
                $success = true;
            }
        }

        $result->{self::SUCCESS_KEY} = $success;
    }

    private function getConfigurationById($input, $result) {
        $success = false;
        $validation = array('id' => 'int');

        if ($this->checkRequest($input, $validation, $result)) {
            $id = $input['id'];

            $results = $this->dbAdapter->getConfigurationById($id);
            if ($results) {
                $result->{'name'} = $results[$this->NAME_INDEX];
                $result->{'configuration'} = json_decode($results[$this->CONFIGURATION_INDEX]);
                $success = true;
            }
        }

        $result->{self::SUCCESS_KEY} = $success;
    }

    private function removeById($input, $result) {
        $success = false;
        $validation = array('id' => 'int');

        if ($this->checkRequest($input, $validation, $result)) {
            $id = $input['id'];

            $configuration = $this->dbAdapter->getConfigurationById($id);
            if ($configuration) {
                if ($this->deleteFilesOnUpdate) {
                    if ($configuration[$this->FILE_INDEX]) { // clean up old file
                        unlink(sprintf('%s/%s', $this->exportPath, $configuration[$this->FILE_INDEX]));
                    }
                }
                $deleteResult = $this->dbAdapter->removeConfigurationById($id);
                $success = $deleteResult;
            }
        }

        $result->{self::SUCCESS_KEY} = $success;
    }

    private function updateById($input, $result) {
        $success = false;
        $validation = array('id' => 'int', 'name' => 'string', 'configuration' => 'json');
        if ($this->checkRequest($input, $validation, $result)) {
            $id = $input['id'];
            $name = $input['name'];
            $conf = $input['configuration'];

            $updateResult = $this->dbAdapter->updateConfigurationById($id, $name, $conf, '');
            if ($updateResult) {
                $success = $updateResult;
            }
        }

        $result->{self::SUCCESS_KEY} = $success;
    }

    private function exportConfigurationToPngById($input, $result) {
        $success = false;
        $validation = array('id' => 'int');
        if ($this->checkRequest($input, $validation, $result)) {
            $id = $input['id'];

            $results = $this->dbAdapter->getConfigurationById($id);
            $configuration = json_decode($results[$this->CONFIGURATION_INDEX]);

            $tableExporter = new TableExporter($this->imagePath, $this->exportPath);
            $file = $tableExporter->exportPNG($configuration);
            if ($file) {
                if ($this->deleteFilesOnUpdate) {
                    $oldFile = $results[$this->FILE_INDEX];
                    if ($oldFile) {
                        unlink(sprintf('%s/%s', $this->exportPath, $file));
                    }
                }
                $this->dbAdapter->updateConfigurationById($id, false, false, $file);
                $result->{'file'} = $file;
                $success = true;
            }
        }

        $result->{self::SUCCESS_KEY} = $success;
    }

    private function exportConfigurationToGifById($input, $result) {
        $success = false;
        $validation = array('id' => 'array');
        if ($this->checkRequest($input, $validation, $result)) {
            $ids = $input['id'];
            $results = $this->dbAdapter->getConfigurations($ids);

            $configurations = array();
            foreach ($results as $res) {
                $configurations []= json_decode($res[$this->CONFIGURATION_INDEX]);
            }
            $tableExporter = new TableExporter($this->imagePath, $this->exportPath);
            $res = $tableExporter->exportGIF($configurations);
            $result->{'file'} = $res;
            $success = true;
        }

        $result->{self::SUCCESS_KEY} = $success;
    }

    private function checkRequest($input, $requiredParams, $result) {
        $errors = array();
        foreach($requiredParams as $param => $type) {
            if (isset($input[$param])) {
                switch ($type) {
                    case 'string':
                        if (empty($input[$param])) $errors []= "Input parameter '{$param}' should not be empty.";
                        break;
                    case 'int':
                        if (!is_numeric($input[$param])) $errors []= "Input parameter '{$param}' should be valid integer.";
                        break;
                    case 'json':
                        if (!json_decode($input[$param])) $errors []= "Input parameter '{$param}' should be valid JSON.";
                        break;
                    case 'array' :
                        if (!is_array($input[$param])) $errors []= "Input parameter '{$param}' should be valid Array.";
                        break;
                }
            }
        }

        if(!empty($errors)) {
            $result->{static::ERRORS_KEY} = $errors;
            return false;
        }

        return true;
    }
}

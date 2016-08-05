<?php
/**----------------------------------------------------------------------------------
* Microsoft Developer & Platform Evangelism
*
* Copyright (c) Microsoft Corporation. All rights reserved.
*
* THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND, 
* EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES 
* OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR PURPOSE.
*----------------------------------------------------------------------------------
* The example companies, organizations, products, domain names,
* e-mail addresses, logos, people, places, and events depicted
* herein are fictitious.  No association with any real company,
* organization, product, domain name, email address, logo, person,
* places, or events is intended or should be inferred.
*----------------------------------------------------------------------------------
**/

/** -------------------------------------------------------------
*
* Azure Table Service Sample - Demonstrate how to perform advanced tasks using the Microsoft Azure Table Service.
*
* Documentation References:
*  - What is a Storage Account - http://azure.microsoft.com/en-us/documentation/articles/storage-whatis-account/
*  - Getting Started with Tables - https://azure.microsoft.com/en-us/documentation/articles/storage-php-how-to-use-table-storage/
*  - Table Service Concepts - http://msdn.microsoft.com/en-us/library/dd179463.aspx
*  - Table Service REST API - http://msdn.microsoft.com/en-us/library/dd179423.aspx
*  - Azure Storage PHP API - https://github.com/Azure/azure-sdk-for-php/
*  - Storage Emulator - http://azure.microsoft.com/en-us/documentation/articles/storage-use-emulator/
*
**/

namespace MicrosoftAzure\Storage\Samples;
require_once "vendor/autoload.php";
require_once "./config.php";
require_once "./random_string.php";

use Config;
use MicrosoftAzure\Storage\Common\ServicesBuilder;
use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\Common\Models\Logging;
use MicrosoftAzure\Storage\Common\Models\Metrics;
use MicrosoftAzure\Storage\Common\Models\RetentionPolicy;
use MicrosoftAzure\Storage\Common\Models\ServiceProperties;
use MicrosoftAzure\Storage\Table\Models\QueryTablesOptions;

class TableAdvancedSamples
{
    public function runAllSamples() {
      $connectionString = Config::getConnectionString();
      $tableService = ServicesBuilder::getInstance()->createTableService($connectionString);

      try {
        echo PHP_EOL;
        echo "* List tables *".PHP_EOL;
        $this->listTables($tableService);

        echo PHP_EOL;
        echo "* Tables service properties *".PHP_EOL;
        $this->tableServiceProperties($tableService);
      }
      catch(ServiceException $e) {
        echo "Error occurred in the sample.".$e->getMessage().PHP_EOL;
      }
    }

    function listTables($tableService) {
      $tablePrefix = "table".generateRandomString();
        
      echo "Create multiple tables with prefix {$tablePrefix}".PHP_EOL;

      for ($i = 1; $i <= 5; $i++) {
        $tableService->createTable($tablePrefix.(string)$i);
      }

      echo "List tables with prefix {$tablePrefix}".PHP_EOL;

      $queryTablesOptions = new QueryTablesOptions();
      $queryTablesOptions->setPrefix($tablePrefix);

      $tablesListResult = $tableService->queryTables($queryTablesOptions);

      foreach ($tablesListResult->getTables() as $table) {
          echo "  table ".$table.PHP_EOL;
      }

      echo "Delete tables with prefix {$tablePrefix}".PHP_EOL;
      for ($i = 1; $i <= 5; $i++) {
        $tableService->deleteTable($tablePrefix.(string)$i);
      }
    }

    // Get and Set Table Service Properties
    function tableServiceProperties($tableService) {
        // Get table service properties
        echo "Get Table Service properties" . PHP_EOL;
        $originalProperties = $tableService->getServiceProperties();

        // Set table service properties
        echo "Set Table Service properties" . PHP_EOL;
        $retentionPolicy = new RetentionPolicy();
        $retentionPolicy->setEnabled(true);
        $retentionPolicy->setDays(10);
        
        $logging = new Logging();
        $logging->setRetentionPolicy($retentionPolicy);
        $logging->setVersion('1.0');
        $logging->setDelete(true);
        $logging->setRead(true);
        $logging->setWrite(true);
        
        $metrics = new Metrics();
        $metrics->setRetentionPolicy($retentionPolicy);
        $metrics->setVersion('1.0');
        $metrics->setEnabled(true);
        $metrics->setIncludeAPIs(true);

        $serviceProperties = new ServiceProperties();
        $serviceProperties->setLogging($logging);
        $serviceProperties->setMetrics($metrics);

        $tableService->setServiceProperties($serviceProperties);
        
        // revert back to original properties
        echo "Revert back to original service properties" . PHP_EOL;
        $tableService->setServiceProperties($originalProperties->getValue());

        echo "Service properties sample completed" . PHP_EOL;
    }
}
?>
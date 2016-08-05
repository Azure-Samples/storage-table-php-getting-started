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
* Azure Table Service Sample - Demonstrate how to perform common tasks using the Microsoft Azure Table Service
* including creating a table, CRUD operations and different querying techniques.
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

use Config;
use MicrosoftAzure\Storage\Common\ServicesBuilder;
use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\Table\Models\Entity;
use MicrosoftAzure\Storage\Table\Models\EdmType;
use MicrosoftAzure\Storage\Table\Models\QueryEntitiesOptions;

class TableBasicSamples
{
    public function runAllSamples() {
      $connectionString = Config::getConnectionString();
      $tableService = ServicesBuilder::getInstance()->createTableService($connectionString);

      try {
        echo PHP_EOL;
        echo "* Basic table operations *".PHP_EOL;
        $this->basicTableOperations($tableService);
      }
      catch(ServiceException $e) {
        echo "Error occurred in the sample.".$e->getMessage().PHP_EOL;
      }
    }

    function basicTableOperations($tableService) {
      $tableName = "tablesample".generateRandomString();

      # Create a new table
      echo "Create a table with name {$tableName}".PHP_EOL;

      $tableService->createTable($tableName);

      $customer = new Entity();
      $customer->setPartitionKey("Harp");
      $customer->setRowKey("1");
      $customer->addProperty("email", EdmType::STRING, "harp@contoso.com");
      $customer->addProperty("phone", EdmType::STRING, "555-555-5555");

      # Insert the entity into the table
      echo "Inserting a new entity into table {$tableName}".PHP_EOL;
      $tableService->insertEntity($tableName, $customer);

      echo "Successfully inserted the new entity".PHP_EOL;

      # Demonstrate how to query the entity
      echo "Read the inserted entity.".PHP_EOL;
      $getCustomerResult = $tableService->getEntity($tableName, "Harp", "1");
      echo $getCustomerResult->getEntity()->getPropertyValue("email").PHP_EOL;
      echo $getCustomerResult->getEntity()->getPropertyValue("phone").PHP_EOL;

      # Demonstrate how to update the entity by changing the phone number
      echo "Update an existing entity by changing the phone number".PHP_EOL;
      $customer = new Entity();
      $customer->setPartitionKey("Harp");
      $customer->setRowKey("1");
      $customer->addProperty("email", EdmType::STRING, "harp@contoso.com");
      $customer->addProperty("phone", EdmType::STRING, "555-555-5555");

      $tableService->updateEntity($tableName, $customer);

      # Demonstrate how to query the updated entity, filter the results with a filter query and select only the value in the phone column
      echo "Read the updated entity with a filter query".PHP_EOL;
      $queryCustomersOption = new QueryEntitiesOptions();
      $queryCustomersOption->setFilter("PartitionKey eq 'Harp'");
      $queryCustomersOption->addSelectField("phone");

      $queryCustomersResult = $tableService->queryEntities($tableName, $queryCustomersOption);
          
      foreach($queryCustomersResult->getEntities() as $customer){
        echo $customer->getPropertyValue("phone").PHP_EOL;
      }
   
      # Demonstrate how to delete an entity
      echo "Delete the entity".PHP_EOL;
      $tableService->deleteEntity($tableName, "Harp", "1");

      echo "Successfully deleted the entity".PHP_EOL;

      # Demonstrate deleting the table, if you don't want to have the table deleted comment the below block of code
      echo "Deleting the table.".PHP_EOL;
      $tableService->deleteTable($tableName);

      echo "Successfully deleted the table".PHP_EOL;
    }
}
?>
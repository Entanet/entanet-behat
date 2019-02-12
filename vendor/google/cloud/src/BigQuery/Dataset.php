<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\BigQuery;

use Google\Cloud\BigQuery\Connection\ConnectionInterface;
use Google\Cloud\Core\ArrayTrait;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Core\Iterator\ItemIterator;
use Google\Cloud\Core\Iterator\PageIterator;

/**
 * [Datasets](https://cloud.google.com/bigquery/what-is-bigquery#datasets) allow
 * you to organize and control access to your tables.
 */
class Dataset
{
    use ArrayTrait;

    /**
     * @var ConnectionInterface Represents a connection to BigQuery.
     */
    private $connection;

    /**
     * @var array The dataset's identity.
     */
    private $identity;

    /**
     * @var array The dataset's metadata.
     */
    private $info;

    /**
     * @var ValueMapper Maps values between PHP and BigQuery.
     */
    private $mapper;

    /**
     * @param ConnectionInterface $connection Represents a connection to
     *        BigQuery.
     * @param string $id The dataset's ID.
     * @param string $projectId The project's ID.
     * @param array $info [optional] The dataset's metadata.
     */
    public function __construct(
        ConnectionInterface $connection,
        $id,
        $projectId,
        ValueMapper $mapper,
        array $info = []
    ) {
        $this->connection = $connection;
        $this->info = $info;
        $this->mapper = $mapper;
        $this->identity = [
            'datasetId' => $id,
            'projectId' => $projectId
        ];
    }

    /**
     * Check whether or not the dataset exists.
     *
     * Example:
     * ```
     * echo $dataset->exists();
     * ```
     *
     * @return bool
     */
    public function exists()
    {
        try {
            $this->connection->getDataset($this->identity + ['fields' => 'datasetReference']);
        } catch (NotFoundException $ex) {
            return false;
        }

        return true;
    }

    /**
     * Delete the dataset.
     *
     * Example:
     * ```
     * $dataset->delete();
     * ```
     *
     * @see https://cloud.google.com/bigquery/docs/reference/v2/datasets/delete Datasets delete API documentation.
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type bool $deleteContents If true, delete all the tables in the
     *           dataset. If false and the dataset contains tables, the request
     *           will fail. **Defaults to** `false`.
     * }
     */
    public function delete(array $options = [])
    {
        $this->connection->deleteDataset($options + $this->identity);
    }

    /**
     * Update the dataset.
     *
     * Example:
     * ```
     * $dataset->update([
     *     'friendlyName' => 'A fanciful dataset.'
     * ]);
     * ```
     *
     * @see https://cloud.google.com/bigquery/docs/reference/v2/datasets/patch Datasets patch API documentation.
     *
     * @param array $metadata The available options for metadata are outlined
     *        at the [Dataset Resource API docs](https://cloud.google.com/bigquery/docs/reference/v2/datasets#resource)
     * @param array $options [optional] Configuration options.
     */
    public function update(array $metadata, array $options = [])
    {
        $options += $metadata;
        $this->info = $this->connection->patchDataset($options + $this->identity);

        return $this->info;
    }

    /**
     * Lazily instantiates a table. There are no network requests made at this
     * point. To see the operations that can be performed on a dataset please
     * see {@see Google\Cloud\BigQuery\Table}.
     *
     * Example:
     * ```
     * $table = $dataset->table('myTableId');
     * ```
     *
     * @param string $id The id of the table to request.
     * @return Table
     */
    public function table($id)
    {
        return new Table(
            $this->connection,
            $id,
            $this->identity['datasetId'],
            $this->identity['projectId'],
            $this->mapper
        );
    }

    /**
     * Fetches tables in the dataset.
     *
     * Example:
     * ```
     * $tables = $dataset->tables();
     *
     * foreach ($tables as $table) {
     *     echo $table->id() . PHP_EOL;
     * }
     * ```
     *
     * @see https://cloud.google.com/bigquery/docs/reference/v2/tables/list Tables list API documentation.
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type int $maxResults Maximum number of results to return per page.
     *     @type int $resultLimit Limit the number of results returned in total.
     *           **Defaults to** `0` (return all results).
     *     @type string $pageToken A previously-returned page token used to
     *           resume the loading of results from a specific point.
     * }
     * @return ItemIterator<Google\Cloud\BigQuery\Table>
     */
    public function tables(array $options = [])
    {
        $resultLimit = $this->pluck('resultLimit', $options, false);

        return new ItemIterator(
            new PageIterator(
                function (array $table) {
                    return new Table(
                        $this->connection,
                        $table['tableReference']['tableId'],
                        $this->identity['datasetId'],
                        $this->identity['projectId'],
                        $this->mapper,
                        $table
                    );
                },
                [$this->connection, 'listTables'],
                $options + $this->identity,
                [
                    'itemsKey' => 'tables',
                    'resultLimit' => $resultLimit
                ]
            )
        );
    }

    /**
     * Creates a table.
     *
     * Example:
     * ```
     * $table = $dataset->createTable('aTable');
     * ```
     *
     * @see https://cloud.google.com/bigquery/docs/reference/v2/tables/insert Tables insert API documentation.
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type array $metadata The available options for metadata are outlined
     *           at the
     *           [Table Resource API docs](https://cloud.google.com/bigquery/docs/reference/v2/tables#resource)
     * }
     * @return Table
     */
    public function createTable($id, array $options = [])
    {
        if (isset($options['metadata'])) {
            $options += $options['metadata'];
            unset($options['metadata']);
        }

        $response = $this->connection->insertTable([
            'projectId' => $this->identity['projectId'],
            'datasetId' => $this->identity['datasetId'],
            'tableReference' => $this->identity + ['tableId' => $id]
        ] + $options);

        return new Table(
            $this->connection,
            $id,
            $this->identity['datasetId'],
            $this->identity['projectId'],
            $this->mapper,
            $response
        );
    }

    /**
     * Retrieves the dataset's details. If no dataset data is cached a network
     * request will be made to retrieve it.
     *
     * Example:
     * ```
     * $info = $dataset->info();
     * echo $info['friendlyName'];
     * ```
     *
     * @see https://cloud.google.com/bigquery/docs/reference/v2/datasets#resource Datasets resource documentation.
     *
     * @param array $options [optional] Configuration options.
     * @return array
     */
    public function info(array $options = [])
    {
        if (!$this->info) {
            $this->reload($options);
        }

        return $this->info;
    }

    /**
     * Triggers a network request to reload the dataset's details.
     *
     * Example:
     * ```
     * $dataset->reload();
     * $info = $dataset->info();
     * echo $info['friendlyName'];
     * ```
     *
     * @see https://cloud.google.com/bigquery/docs/reference/v2/datasets/get Datasets get API documentation.
     *
     * @param array $options [optional] Configuration options.
     * @return array
     */
    public function reload(array $options = [])
    {
        return $this->info = $this->connection->getDataset($options + $this->identity);
    }

    /**
     * Retrieves the dataset's ID.
     *
     * Example:
     * ```
     * echo $dataset->id();
     * ```
     *
     * @return string
     */
    public function id()
    {
        return $this->identity['datasetId'];
    }

    /**
     * Retrieves the dataset's identity.
     *
     * An identity provides a description of a resource that is nested in nature.
     *
     * Example:
     * ```
     * echo $dataset->identity()['projectId'];
     * ```
     *
     * @return array
     */
    public function identity()
    {
        return $this->identity;
    }
}

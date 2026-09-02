<?php

namespace App\Services\Parse\DataProvider;

use App\Services\Parse\Interfaces\DataProviderInterface;
use GuzzleHttp\Client as GuzzleClient;
use Exception;

abstract class AbstractDataProvider implements DataProviderInterface
{
    protected string $endpoint;
    protected int $batchSize = 500;
    protected int $maxRetries = 3;
    protected int $retryDelay = 5;

    public function __construct(protected GuzzleClient $client)
    {
        //
    }

    public function get(array $additionalParams = []): void
    {
        $this->persist($this->collect($additionalParams));
    }

    /** Получает полный набор данных из API, не изменяя БД. */
    public function collect(array $additionalParams = []): array
    {
        $page = 1;
        $preparedData = [];

        while (true) {
            try {
                $queryParams = array_merge([
                    'page' => $page,
                    'per_page' => 100,
                ], $additionalParams);

                $response = $this->client->get($this->endpoint, [
                    'query' => $queryParams
                ]);

                $responseBody = json_decode($response->getBody(), true);
                $data = $responseBody['list'] ?? [];

                if (empty($data)) {
                    break;
                }

                foreach ($data as $item) {
                    $preparedData[] = $this->prepareData($item);
                }

                $page++;
                echo "Processed page {$page}, total records: " . ($page * 100) . "\n";
                sleep(2);

            } catch (Exception $e) {
                throw $e;
            }
        }

        return $preparedData;
    }

    /** Сохраняет уже загруженные данные; может выполняться внутри общей транзакции. */
    public function persist(array $dataToSave): void
    {
        foreach (array_chunk($dataToSave, $this->batchSize) as $batch) {
            $this->saveWithRetry($batch);
        }

        echo "Import completed!\n";
    }

    protected function saveWithRetry(array $dataToSave, int $attempt = 1): void
    {
        try {
            $this->save($dataToSave);
        } catch (Exception $e) {
            if ($attempt >= $this->maxRetries) {
                throw new Exception("Failed to save after {$this->maxRetries} attempts: " . $e->getMessage());
            }

            sleep($this->retryDelay * $attempt);
            $this->saveWithRetry($dataToSave, $attempt + 1);
        }
    }

    abstract protected function prepareData(array $item): array;
    abstract protected function save(array $dataToSave): void;
}

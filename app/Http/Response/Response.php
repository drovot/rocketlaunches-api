<?php

declare(strict_types=1);

namespace App\Http\Response;

use App\Models\AbstractModel;
use Illuminate\Http\JsonResponse;

class Response extends \Illuminate\Http\Response
{

    /** @var mixed */
    private $result;

    /** @var int|string */
    private $total = 0;

    /** @var string|null  */
    private ?string $errorMessage = null;

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return int|string
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @param mixed $result
     * @return Response
     */
    public function setResult($result): Response
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @param int|string $total
     * @return Response
     */
    public function setTotal($total): Response
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @param string|null $errorMessage
     * @return Response
     */
    public function setErrorMessage(?string $errorMessage): Response
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    private function recursiveExport($data): array
    {
        if ($data instanceof AbstractModel) {
            return $this->result->export();
        }

        if (!is_array($data)) {
            return [];
        }

        foreach ($data as $key=>$item) {
            if (is_array($item)) {
                $data[$key] = $this->recursiveExport($item);
            }

            if ($item instanceof AbstractModel) {
                $data[$key] = $item->export();
            }
        }

        return $data;
    }

    public function build(): JsonResponse
    {
        $output = [
            "success" => $this->statusCode === 200,
            "method" => \request()->route()[1]['as'],
            "statusCode" => $this->statusCode,
            "statusText" => $this->statusText
        ];

        if ($this->errorMessage !== null) {
            $output["errorMessage"] = $this->errorMessage;
        }

        if (!is_object($this->result) && (is_array($this->result) || is_countable($this->result))) {
            $output["count"] = count($this->result);
        }

        if ($this->total > 0) {
            $output["total"] = (int) $this->total;
        }

        $this->result = $this->recursiveExport($this->result);

        if ($this->result !== null) {
            $output["result"] = $this->result;
        }

        return response()->json($output);
    }
}

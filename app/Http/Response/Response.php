<?php

declare(strict_types=1);

namespace App\Http\Response;

use App\Http\Managers\Defaults;
use App\Models\AbstractModel;
use App\Tracking\TrackingManager;
use Illuminate\Http\JsonResponse;

class Response extends \Illuminate\Http\Response
{

    private const URL_TEMPLATE = '%s%s?limit=%s&page=%s&detailed=%s';

    /** @var float $executionStart */
    private float $executionStart;

    /** @var float $executionEnd */
    private float $executionEnd;

    /** @var mixed */
    private $result;

    /** @var int|string */
    private $total = 0;

    /** @var string|null */
    private ?string $errorMessage = null;

    /** @var string|null */
    private ?string $trackingId = null;

    /** @var array */
    private array $requestParameters;

    public function __construct($content = '', $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);
        $this->executionStart = gettimeofday(true);
        $this->requestParameters = request()->request->all();
    }

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
     * @return string|null
     */
    public function getTrackingId(): ?string
    {
        return $this->trackingId;
    }

    /**
     * get execution time in ms
     *
     * @return int|null
     */
    public function getExecutionTime(): ?int
    {
        if (
            $this->executionStart === null
            || $this->executionEnd === null
        ) {
            return null;
        }

        return (int) (($this->executionEnd - $this->executionStart) * 1000);
    }

    /**
     * @return float
     */
    public function getExecutionStart(): float
    {
        return $this->executionStart;
    }

    /**
     * @return float
     */
    public function getExecutionEnd(): float
    {
        return $this->executionEnd;
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

    /**
     * @param string|null $trackingId
     * @return self
     */
    public function setTrackingId(?string $trackingId): self
    {
        $this->trackingId = $trackingId;

        return $this;
    }

    // if $next is false, the last url will generate
    private function generateUrl(bool $next): ?string
    {
        $limit = $this->requestParameters["limit"] ?? Defaults::REQUEST_LIMIT;
        $page = $this->requestParameters["page"] ?? Defaults::REQUEST_PAGE;
        $detailed = $this->requestParameters["detailed"] ?? Defaults::REQUEST_DETAILED;

        if (!$next && $page === 1) {
            return null;
        }

        return sprintf(
            self::URL_TEMPLATE,
            env("APP_URL"),
            request()->server->get("PATH_INFO"),
            $limit,
            $next ? ($page + 1) : ($page - 1),
            $detailed ? '1' : '0'
        );
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
        $this->executionEnd = gettimeofday(true);

        if ($this->trackingId !== null) {
            $trackingManager = new TrackingManager();
            $trackingManager->update($this->trackingId, $this, $this->requestParameters["detailed"] ?? null);
        }

        $output = [
            "success" => $this->statusCode >= 200 && $this->statusCode <= 299,
            "method" => \request()->route()[1]['as'],
            "statusCode" => $this->statusCode,
            "statusText" => $this->statusText
        ];

        if ($this->errorMessage !== null) {
            $output["errorMessage"] = $this->errorMessage;
        }

        if (!is_object($this->result) && (is_array($this->result) || is_countable($this->result))) {
            $output["count"] = count($this->result);
            $output["last"] = $this->generateUrl(false);
            $output["next"] = $this->generateUrl(true);
        }

        if ($this->total > 0) {
            $output["total"] = (int) $this->total;
        }

        $this->result = $this->recursiveExport($this->result);

        if ($this->result !== null && !empty($this->result)) {
            $output["result"] = $this->result;
        }

        return response()->json($output, $this->statusCode);
    }
}

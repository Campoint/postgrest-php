<?php

declare(strict_types=1);

namespace PostgrestPhp\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * PostgrestResponse holds the response from the PostgREST server.
 */
class PostgrestResponse
{
    private string $body;

    private int $statusCode;

    /**
     * @var array<array<string>>
     */
    private array $headers;

    /**
     * @var null|array<array-key,mixed>
     */
    private ?array $parsedBody;

    /**
     * @var array<string, string|int|float>
     */
    private array $location;

    private int $contentRangeStart = 0;

    private int $contentRangeEnd = 0;

    private int $contentRangeTotal = 0;

    /**
     * Create a new PostgrestResponse.
     *
     * @param ResponseInterface $response The response from the PostgREST server.
     */
    public function __construct(
        private ResponseInterface $response,
    ) {
        $this->body = $this->response->getBody()
            ->getContents();
        $this->statusCode = $this->response->getStatusCode();
        $this->headers = $this->response->getHeaders();
        $this->parsedBody = json_decode($this->body, true);

        $this->parseLocationHeader();
        $this->parseContentRangeHeader();
    }

    /**
     * Get the location for the column using Location header of the response.
     *
     * @param string $columnName The name of the column.
     * @return string|int|float|null The location for the column.
     */
    public function getLocation(string $columnName): string|int|float|null
    {
        if (! isset($this->location[$columnName])) {
            return null;
        }
        if (is_numeric($this->location[$columnName])) {
            return +$this->location[$columnName];
        }
        return $this->location[$columnName];
    }

    /**
     * Get the start of the range using Content-Range header of the response.
     *
     * @return int The start of the range.
     */
    public function getRangeStart(): int
    {
        return $this->contentRangeStart;
    }

    /**
     * Get the end of the range using Content-Range header of the response.
     *
     * @return int The end of the range.
     */
    public function getRangeEnd(): int
    {
        return $this->contentRangeEnd;
    }

    /**
     * Get the total of the range using Content-Range header of the response.
     *
     * @return int The total of the range.
     */
    public function getRangeTotal(): int
    {
        return $this->contentRangeTotal;
    }

    /**
     * Get the status code of the response.
     *
     * @return int The status code of the response.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the raw result of the response.
     *
     * @return string The raw result of the response.
     */
    public function rawResult(): string
    {
        return $this->body;
    }

    /**
     * Get the headers of the response.
     *
     * @return array<array<string>> The headers of the response.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get the parsed body of the response.
     *
     * @return array<mixed>|array<array-key, mixed>|null The parsed body of the response.
     */
    public function result(): ?array
    {
        return $this->parsedBody;
    }

    /**
     * Parse the Location header of the response.
     */
    private function parseLocationHeader(): void
    {
        if (! isset($this->headers['Location'])) {
            return;
        }
        $loc = $this->headers['Location'][0];
        $preParsed = parse_url($loc, PHP_URL_QUERY);
        if (! is_string($preParsed)) {
            return;
        }
        parse_str($preParsed, $parsedHeader);
        foreach ($parsedHeader as $key => $value) {
            if (! is_string($value) || ! is_string($key)) {
                continue;
            }
            $this->location[$key] = substr($value, 3);
        }
    }

    /**
     * Parse the Content-Range header of the response.
     */
    private function parseContentRangeHeader(): void
    {
        if (! isset($this->headers['Content-Range'])) {
            return;
        }
        $contentRange = $this->headers['Content-Range'][0];
        $contentRange = explode('/', $contentRange);
        /** @phpstan-ignore-next-line */
        if (! is_array($contentRange) || ! count($contentRange) === 2) {
            return;
        }
        $startEnd = explode('-', $contentRange[0]);
        $this->parseRange($startEnd, $contentRange);
    }

    /**
     * Parse the range of the Content-Range header of the response.
     *
     * @param string[] $startEnd The start and end of the range.
     * @param string[] $contentRange The content range.
     */
    private function parseRange(array $startEnd, array $contentRange): void
    {
        if (count($startEnd) !== 2 && $contentRange[0] !== '*') {
            //noop
        } elseif ($contentRange[0] === '*') {
            $this->contentRangeStart = 0;
            $this->contentRangeEnd = is_numeric($contentRange[1]) ? (int) $contentRange[1] : 0;
            return;
        }
        $this->contentRangeStart = is_numeric($startEnd[0]) ? (int) $startEnd[0] : 0;
        $this->contentRangeEnd = is_numeric($startEnd[1]) ? (int) $startEnd[1] : 0;
        $this->contentRangeTotal = is_numeric($contentRange[1]) ? (int) $contentRange[1] : 0;
    }
}

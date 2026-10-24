<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use Codeception\Test\Unit;
use humhub\components\Response;

/**
 * Covers the security headers applied by {@see Response::prepare()}.
 *
 * @since 1.20
 */
class ResponseTest extends Unit
{
    private const HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'sameorigin',
        'Content-Security-Policy' => "default-src 'self'",
    ];

    private function prepared(string $format, array $headers = self::HEADERS, $data = 'test'): Response
    {
        $response = new Response(['defaultHeaders' => $headers]);
        $response->format = $format;
        $response->data = $data;
        $response->prepare();

        return $response;
    }

    public function testHtmlDocumentGetsEveryHeader()
    {
        $headers = $this->prepared(Response::FORMAT_HTML)->headers;

        $this->assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        $this->assertSame('sameorigin', $headers->get('X-Frame-Options'));
        $this->assertSame("default-src 'self'", $headers->get('Content-Security-Policy'));
    }

    /**
     * A Content-Security-Policy governs a document. Sending one with a JSON payload protects
     * nothing, so it is left out while the remaining headers still apply.
     */
    public function testNonDocumentKeepsHeadersButDropsThePolicy()
    {
        $headers = $this->prepared(Response::FORMAT_JSON, self::HEADERS, ['a' => 1])->headers;

        $this->assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        $this->assertSame('sameorigin', $headers->get('X-Frame-Options'));
        $this->assertNull($headers->get('Content-Security-Policy'));
    }

    public function testReportOnlyPolicyIsTreatedLikeThePolicy()
    {
        $headers = ['Content-Security-Policy-Report-Only' => "default-src 'self'"];

        $this->assertNotNull($this->prepared(Response::FORMAT_HTML, $headers)->headers
            ->get('Content-Security-Policy-Report-Only'));
        $this->assertNull($this->prepared(Response::FORMAT_JSON, $headers, ['a' => 1])->headers
            ->get('Content-Security-Policy-Report-Only'));
    }

    public function testEmptyHeaderValuesAreNotSent()
    {
        $headers = $this->prepared(Response::FORMAT_HTML, ['X-Frame-Options' => ''])->headers;

        $this->assertNull($headers->get('X-Frame-Options'));
    }

    /**
     * Without the placeholder no nonce is created at all - a header carrying it is the only
     * switch there is.
     */
    public function testNoNonceWithoutThePlaceholder()
    {
        $response = $this->prepared(Response::FORMAT_HTML);

        $this->assertNull($response->getNonce());
        $this->assertFalse($response->isCspReportingEnabled());
    }

    public function testReportingIsDetectedFromThePlaceholder()
    {
        $response = new Response([
            'defaultHeaders' => ['Content-Security-Policy' => 'default-src *; report-uri ' . Response::REPORT_URI_PLACEHOLDER],
        ]);

        $this->assertTrue($response->isCspReportingEnabled());
    }
}

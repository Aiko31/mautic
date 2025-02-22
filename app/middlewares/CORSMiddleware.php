<?php

namespace Mautic\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CORSMiddleware implements HttpKernelInterface, PrioritizedMiddlewareInterface
{
    use ConfigAwareTrait;

    public const PRIORITY = 1000;

    /**
     * @var array
     */
    protected $corsHeaders = [
        'Access-Control-Allow-Origin'      => '*',
        'Access-Control-Allow-Headers'     => 'Origin, X-Requested-With, Content-Type, Authorization',
        'Access-Control-Allow-Methods'     => 'PUT, GET, POST, DELETE, OPTIONS',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 10 * 60 * 60, // 10 min, max age for Chrome
    ];

    /**
     * @var bool
     */
    protected $requestOriginIsValid = false;

    /**
     * @var bool
     */
    protected $restrictCORSDomains = true;

    /**
     * @var array
     */
    protected $validCORSDomains = [];

    /**
     * @var HttpKernelInterface
     */
    protected $app;

    public function __construct(HttpKernelInterface $app)
    {
        $this->app                 = $app;
        $this->config              = $this->getConfig();
        $this->restrictCORSDomains = array_key_exists('cors_restrict_domains', $this->config) ? (bool) $this->config['cors_restrict_domains'] : true;
        $this->validCORSDomains    = array_key_exists('cors_valid_domains', $this->config) ? (array) $this->config['cors_valid_domains'] : [];
    }

    public function handle(Request $request, $type = self::MAIN_REQUEST, $catch = true): Response
    {
        $this->corsHeaders['Access-Control-Allow-Origin'] = $this->getAllowOriginHeaderValue($request);

        // Capture all OPTIONS requests
        if ('OPTIONS' === $request->getMethod()) {
            $response = new Response('', Response::HTTP_NO_CONTENT);

            // If this is a valid OPTIONS request, set the CORS headers on the Response and exit.
            if (
                $this->requestOriginIsValid
                && $request->headers->has('Access-Control-Request-Headers')
                && $request->headers->has('Origin')
            ) {
                foreach ($this->corsHeaders as $header => $value) {
                    $response->headers->set($header, $value);
                }
            }

            return $response;
        }

        $response = $this->app->handle($request, $type, $catch);

        // Add standard CORS headers to any XHR
        if ($request->isXmlHttpRequest()) {
            foreach ($this->corsHeaders as $header => $value) {
                $response->headers->set($header, $value);
            }
        }

        return $response;
    }

    /**
     * Get the value for the Access-Control-Allow-Origin header
     * based on the Request and local configuration options.
     *
     * @return string|null
     */
    private function getAllowOriginHeaderValue(Request $request)
    {
        $origin = $request->headers->get('Origin');

        // If we're not restricting domains, set the header to the request origin
        if (!$this->restrictCORSDomains || in_array($origin, $this->validCORSDomains)) {
            $this->requestOriginIsValid = true;

            return $origin;
        }

        // Check the domains using shell wildcard patterns
        $validCorsDomainFilter = function ($validCorsDomain) use ($origin) {
            if (null === $origin) {
                return null;
            }

            return fnmatch($validCorsDomain, $origin, FNM_CASEFOLD);
        };

        if (array_filter($this->validCORSDomains, $validCorsDomainFilter)) {
            $this->requestOriginIsValid = true;
            $this->corsHeaders['Vary']  = 'Origin';

            return $origin;
        }

        $this->requestOriginIsValid = false;

        return null;
    }

    public function getPriority()
    {
        return self::PRIORITY;
    }
}

<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ApiAuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (strtolower($request->getMethod()) === 'options') return;

        $provided = $request->getHeaderLine('X-API-Key');
        if (!$provided) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON(['status' => false, 'message' => 'API key tidak ditemukan']);
        }

        // .env: API_KEY_HASH="sha256:xxxxxxxx"
        $env = getenv('API_KEY_HASH'); // format "sha256:<hex>"
        if (!$env || strpos($env, 'sha256:') !== 0) {
            return Services::response()->setStatusCode(500)
                ->setJSON(['status' => false, 'message' => 'Perlu konfigurasi key di server']);
        }

        $expectedHash = substr($env, 7); // potong "sha256:"
        $providedHash = hash('sha256', $provided);

        if (!hash_equals($expectedHash, $providedHash)) {
            return Services::response()
                ->setStatusCode(403)
                ->setJSON(['status' => false, 'message' => 'API key tidak valid']);
        }

    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}

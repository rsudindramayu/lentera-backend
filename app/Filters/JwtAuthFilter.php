<?php

namespace App\Filters;

use App\Services\AuthService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class JwtAuthFilter implements FilterInterface
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
        //allow CORS preflight
        if (strtolower($request->getMethod()) == 'options') {
            return;
        }

        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || stripos($authHeader, 'Bearer ') === false) {
            return Services::response()->setStatusCode(401)->setJSON([
                'status' => false,
                'message' => 'Access token tidak ditemukan!',
            ]);
        }

        $token = trim(substr($authHeader, 7));
        $auth = new AuthService();
        try {
            $decodeToken = $auth->verifyAccessToken($token);
            if (!$decodeToken) {
                return Services::response()->setStatusCode(401)->setJSON([
                    'status' => false,
                    'message' => 'Token tidak valid atau kadaluarsa!',
                ]);
            }

        } catch (\Exception $e) {
            $code = (int) ($e->getCode() ?: 401);
            if ($code < 400 || $code > 499) $code = 401;

            return Services::response()
                ->setStatusCode($code)
                ->setJSON(['status' => false, 'message' => $e->getMessage()]);
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

<?php

namespace App\Controllers\Api\V1;

use App\Services\AuthService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Services\PenggunaService;
use Config\Services;

class AuthController extends ResourceController
{
    protected $authService;
    protected $penggunaService;
    protected $validator;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->penggunaService = new PenggunaService();
        $this->validator = Services::validation();
    }

    public function signIn()
    {
        try {
            $validator = $this->validator->setRules([
                'username' => 'required',
                'password' => 'required'
            ]);

            if ($validator->withRequest($this->request)->run() === false) {
                return $this->respond([
                    'status' => false,
                    'message' => $validator->getErrors(),
                ], ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $json = $this->request->getJSON();
            $user = $this->penggunaService->validasiPengguna($json->username, $json->password);

            if (!$user) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Username atau password salah!',
                ], ResponseInterface::HTTP_UNAUTHORIZED);
            }

            $user['roles'] = $this->penggunaService->getRolesWithPermissions($user['ID']);
            $dataUser = $this->penggunaService->getDataUser($user);
            $accessToken = $this->authService->generateAccessToken($user);
            $refreshToken = $this->authService->generateRefreshToken($user['ID']);

            return $this->respond([
                'status' => true,
                'message' => 'Login sukses!',
                'data' => [
                    'accessToken' => $accessToken,
                    'refreshToken' => $refreshToken,
                    'user' => $dataUser,
                    'roles' => $user['roles'],
                ]
            ], ResponseInterface::HTTP_OK);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage(),
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function refreshToken()
    {
        //validasi request refresh token
        $this->validator->setRules([
            'refreshToken' => 'required'
        ]);

        if ($this->validator->withRequest($this->request)->run() === false) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try {
            $body = $this->request->getJSON();

            //cek refresh token valid?
            $isRefreshTokenValid = $this->authService->validateRefreshToken($body->refreshToken);
            if (!$isRefreshTokenValid) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Refresh token tidak valid!',
                ], ResponseInterface::HTTP_UNAUTHORIZED);
            }

            //get data pengguna
            $cekUser = $this->penggunaService->getData([
                'where' => [
                    'ID' => $isRefreshTokenValid['pengguna_id'],
                    'STATUS' => 1,
                ],
                'limit' => 1
            ]);

            if (count($cekUser) < 0) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Pengguna tidak ditemukan!',
                ], ResponseInterface::HTTP_UNAUTHORIZED);
            }

            $user = $cekUser[0];
            $user['roles'] = $this->penggunaService->getRolesWithPermissions($user['ID']);
            $accessToken = $this->authService->generateAccessToken($user);

            return $this->respond([
                'status' => true,
                'message' => 'Refresh token sukses!',
                'data' => [
                    'accessToken' => $accessToken,
                ]
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage(),
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function signOut()
    {
        try {
            $body = $this->request->getJSON();
            if (empty($body->refreshToken)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Refresh token tidak valid!',
                ], ResponseInterface::HTTP_UNAUTHORIZED);
            }

            // Cek dan hapus token di database
            $isRemoveSuccess = $this->authService->removeRefreshToken($body->refreshToken);
            if (!$isRemoveSuccess) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Refresh token tidak ada atau anda telah logout!',
                ], ResponseInterface::HTTP_UNAUTHORIZED);
            }

            return $this->respond([
                'status' => true,
                'message' => 'Logout sukses!',
            ], ResponseInterface::HTTP_OK);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage(),
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function fetchUser()
    {
        try {
            // Ambil data token JWT dari header Authorization
            $authHeader = $this->request->getHeaderLine('Authorization');

            if (!$authHeader || !str_contains($authHeader, 'Bearer ')) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Access token tidak ditemukan!',
                ], ResponseInterface::HTTP_UNAUTHORIZED);
            }

            $token = trim(str_replace('Bearer', '', $authHeader));
            $isAccessTokenValid = $this->authService->verifyAccessToken($token);

            if (!$isAccessTokenValid) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Token tidak valid atau kadaluarsa!',
                ], ResponseInterface::HTTP_UNAUTHORIZED);
            }

            $resultPengguna = $this->penggunaService->getData([
                'where' => [
                    'ID' => $isAccessTokenValid->uid,
                    'STATUS' => 1,
                ],
                'limit' => 1
            ]);

            if (count($resultPengguna) < 0) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Pengguna tidak ditemukan!',
                ], ResponseInterface::HTTP_UNAUTHORIZED);
            }

            $user = $resultPengguna[0];
            $user['roles'] = $this->penggunaService->getRolesWithPermissions($user['ID']);
            $dataUser = $this->penggunaService->getDataUser($user);

            return $this->respond([
                'status' => true,
                'message' => 'Pengguna ditemukan',
                'data' => [
                    'user' => $dataUser,
                    'roles' => $user['roles'],
                ]
            ], ResponseInterface::HTTP_OK);
        } catch (\Exception $e) {
            $code = $e->getCode() === 401 ? ResponseInterface::HTTP_UNAUTHORIZED : ResponseInterface::HTTP_INTERNAL_SERVER_ERROR;
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }
}

<?php

namespace App\Services;

use App\Models\PenggunaModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\UserModel;
use App\Models\RefreshTokenModel;

class AuthService
{
    protected $jwtKey;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_KEY');
    }

    public function generateAccessToken($user)
    {
        $payload = [
            'iss' => 'localhost',
            'aud' => 'localhost',
            'iat' => time(),
            'exp' => time() + 900, // 15 minutes
            'uid' => $user['ID'],
            'roles' => $user['roles'],
        ];

        return JWT::encode($payload, $this->jwtKey, 'HS256');
    }

    public function generateRefreshToken($userId)
    {
        $token = bin2hex(random_bytes(64));
        $model = new RefreshTokenModel();
        $model->insert([
            'pengguna_id' => $userId,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 604800) // 7 hari
        ]);
        return $token;
    }

    public function verifyAccessToken($token)
    {
        $decodeToken =  JWT::decode($token, new Key($this->jwtKey, 'HS256'));

        //cek apakah token expired
        if ($decodeToken->exp < time()) {
            return false;
        }

        //cek apakah token dapat digunakan
        if ($decodeToken->iat > time()) {
            return false;
        }

        //cek apakah user ada
        $penggunaModel = new PenggunaModel();
        $user = $penggunaModel->where('ID', $decodeToken->uid)->where('STATUS', 1)->first();
        if (!$user) {
            return false;
        }
    }

    public function validateRefreshToken($token)
    {
        $model = new RefreshTokenModel();
        return $model->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
    }

    public function removeRefreshToken($token)
    {
        $model = new RefreshTokenModel();
        $isTokenExist = $model->where('token', $token)->first();
        if (!$isTokenExist) {
            return false;
        }
        $model->where('token', $token)->delete();
        return true;
    }
}

<?php

namespace App\Modules\AntreanOnline\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Modules\AntreanOnline\Services\ReservasiService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ReservasiController extends ResourceController
{
    protected $reservasiService;
    protected $validation;

    public function __construct()
    {
        $this->reservasiService = new ReservasiService();
        $this->validation = Services::validation();
    }

    public function getReservasi()
    {
        try {
            $params = $this->request->getGet();
            $reservasi = $this->reservasiService->getData($params);
            if (!$reservasi['status']) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Data reservasi tidak ditemukan!'
                ], ResponseInterface::HTTP_NOT_FOUND);
            }
            return $this->respond([
                'status' => true,
                'message' => 'Data reservasi ditemukan!',
                'data' => $reservasi['data']
            ], ResponseInterface::HTTP_OK);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getSummaryReservasi()
    {
        try {
            $params = $this->request->getGet();
            $data = $this->reservasiService->getSummaryReservasi($params);
            if (!$data['status']) {
                return $this->respond([
                    'status' => false,
                    'message' => $data['message']
                ], ResponseInterface::HTTP_NOT_FOUND);
            }

            return $this->respond([
                'status' => true,
                'message' => 'Data reservasi ditemukan!',
                'data' => $data['data']
            ], ResponseInterface::HTTP_OK);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function batalkanReservasi()
    {
        /*Batalkan kodebooking reservasi di BPJS dan di local
        jika onBPJS dan onLocal = tidak dikirim maka batalkan reservasi di BPJS dan di local
        */

        $this->validation->setRules([
            'kodeBooking' => 'required',
        ]);
        if (!$this->validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validation->getErrors(),
            ], 400);
        }

        try {
            $body = $this->request->getBody();
            $params = json_decode($body, true);
            $request = $this->reservasiService->batalkanReservasi($params);
            if ($request['status']) {
                return $this->respond([
                    'status' => true,
                    'message' => 'Reservasi berhasil dibatalkan!',
                ], ResponseInterface::HTTP_OK);
            }
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function batalkanReservasiMasal()
    {
        $this->validation->setRules([
            'kodeBookings' => 'required|is_array',
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validation->getErrors(),
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $body = $this->request->getBody();
            $data = json_decode($body, true);
            if (is_array($data['kodeBookings']) && count($data['kodeBookings']) > 0) {
                $response = $this->reservasiService->batalkanReservasiMassal($data['kodeBookings']);
                return $this->respond([
                    'status' => true,
                    'message' => 'Pembatalan reservasi selesai!',
                    'data' => $response['data']
                ], ResponseInterface::HTTP_OK);
            }
            return $this->respond([
                'status' => false,
                'message' => 'Data reservasi tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

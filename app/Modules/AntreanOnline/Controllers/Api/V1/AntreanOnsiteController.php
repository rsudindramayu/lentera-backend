<?php

namespace App\Modules\AntreanOnline\Controllers\Api\V1;

use App\Modules\AntreanOnline\Services\AntreanOnsiteService;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;

class AntreanOnsiteController extends ResourceController
{
    protected $antreanOnsiteService;
    protected $validation;

    public function __construct()
    {
        $this->antreanOnsiteService = new AntreanOnsiteService();
        $this->validation = Services::validation();
    }

    public function getDataPasien($keyPasien)
    {
        try {
            $response = $this->antreanOnsiteService->getDataPasien($keyPasien);
            if ($response['status']) return $this->respond([
                'status' => true,
                'message' => 'Data pasien ditemukan!',
                'data' => $response['data']
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'Data pasien tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getHistoryPendaftaran($norm)
    {
        try {
            $response = $this->antreanOnsiteService->getHistoryPendaftaran($norm);
            if ($response['status']) return $this->respond([
                'status' => true,
                'message' => 'History pasien ditemukan!',
                'data' => [
                    'list' => $response['data']
                ]
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'response pasien tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getReservasi($norm)
    {
        try {
            $response = $this->antreanOnsiteService->getReservasi($norm);
            if ($response['status']) return $this->respond([
                'status' => true,
                'message' => 'Reservasi pasien ditemukan!',
                'data' => [
                    'list' => $response['data']
                ]
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'Reservasi pasien tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getPoliklinik()
    {
        try {
            $response = $this->antreanOnsiteService->getPoliklinik();
            if ($response['status']) return $this->respond([
                'status' => true,
                'message' => 'Data poliklinik ditemukan!',
                'data' => [
                    'list' => $response['data']
                ]
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'Data poliklinik tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getJadwalDokter()
    {
        $this->validation->setRules([
            'tanggal' => 'required|valid_date[Y-m-d]',
            'kodePoli' => 'required',
        ]);
        if (!$this->validation->withRequest($this->request)->run()) return $this->respond([
            'status' => false,
            'message' => $this->validation->getErrors()
        ], ResponseInterface::HTTP_BAD_REQUEST);
        try {
            $params = $this->request->getGet();
            $response = $this->antreanOnsiteService->getJadwalDokter($params);
            if ($response['status']) return $this->respond([
                'status' => true,
                'message' => 'Jadwal dokter ditemukan!',
                'data' => $response['data']
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'Jadwal dokter tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createAntreanOnsite()
    {
        // $this->validator->setRules([
        //     'TANGGALKUNJUNGAN' => 'required',
        //     'NORM' => 'required',
        //     'NIK' => 'required',
        //     'NAMA' => 'required',
        //     'TANGGAL_LAHIR' => 'required',
        //     'JK' => 'required',
        //     'POLI' => 'required',
        //     'DOKTER' => 'required',
        //     'NO_KARTU_BPJS' => 'required',
        //     'NO_REF_BPJS' => 'required',
        //     'REF_JADWAL' => 'required',
        //     'JAM_PRAKTEK' => 'required',
        // ]);

        // if (!$this->validator->withRequest($this->request)->run()) {
        //     return $this->response->setJSON([
        //         'status' => false,
        //         'message' => $this->validator->getErrors(),
        //     ])->setStatusCode(400);
        // }

        // try {
        //     $body = $this->request->getBody();
        //     $data = json_decode($body, true);
        //     $nomorBooking = $this->reservasiModel->generateKodeBooking($data['TANGGALKUNJUNGAN']);
        //     $nomorAntrean = $this->reservasiModel->generateNomorAntrean('A', $data['TANGGALKUNJUNGAN'], 2);
        //     $nomorAntreanPoli = $this->reservasiModel->generateNomorAntreanPoli($data['ID_RUANGAN'], $data['TANGGALKUNJUNGAN']);
        //     $nomorAntreanBPJS = $this->reservasiModel->generateNomorAntreanBPJS($data['TANGGALKUNJUNGAN'], $data['POLI'], $data['DOKTER'], $data['JAM_PRAKTEK']);
        //     $jam = $this->reservasiModel->generateJamAntrean($nomorAntrean, 'A', $data['ID_RUANGAN'], $data['TANGGALKUNJUNGAN']);
        //     $jamPelayanan = $this->reservasiModel->generateJamPelayanan($nomorAntrean, 'A', $data['ID_RUANGAN'], $data['TANGGALKUNJUNGAN']);
        //     $antrean = [
        //         'ID' => $nomorBooking ?? null,
        //         'TANGGALKUNJUNGAN' => $data['TANGGALKUNJUNGAN'] ?? null,
        //         'TANGGAL_REF' => $data['TANGGAL_REF'] ?? null,
        //         'NORM' => $data['NORM'] ?? null,
        //         'NIK' => $data['NIK'] ?? null,
        //         'NAMA' => $data['NAMA'] ?? null,
        //         'TANGGAL_LAHIR' => $data['TANGGAL_LAHIR'] ?? null,
        //         'TEMPAT_LAHIR' => $data['TEMPAT_LAHIR'] ?? null,
        //         'ALAMAT' => $data['ALAMAT'] ?? null,
        //         'JK' => $data['JK'] ?? null,
        //         'POLI' => $data['ID_RUANGAN'] ?? null,
        //         'NAMA_RUANGAN' => $this->getNamaPoliBPJS($data['POLI']) ?? $data['NAMA_RUANGAN'] ?? null,
        //         'POLI_BPJS' => $data['POLI'] ?? null,
        //         'REF_POLI_RUJUKAN' => $data['POLI'] ?? null,
        //         'DOKTER' => $data['DOKTER'] ?? null,
        //         'NAMA_DOKTER' => $data['NAMA_DOKTER'] ?? null,
        //         'CARABAYAR' => 2 ?? null,
        //         'JENIS_KUNJUNGAN' => 3 ?? null,
        //         'NO_KARTU_BPJS' => $data['NO_KARTU_BPJS'] ?? null,
        //         'CONTACT' => $data['CONTACT'] ?? null,
        //         'TGL_DAFTAR' => $data['TANGGAL_REF'] ?? null,
        //         'NO' => $nomorAntrean ?? null,
        //         'ANTRIAN_POLI' => $nomorAntreanPoli ?? null,
        //         'NOMOR_ANTRIAN' => $nomorAntreanPoli ?? null,
        //         'JAM' => $jam ?? null,
        //         'JAM_PELAYANAN' => $jamPelayanan ?? null,
        //         'POS_ANTRIAN' => 'A' ?? null,
        //         'JENIS' => 1,
        //         'JADWAL_DOKTER' => 0,
        //         'NO_REF_BPJS' => $data['NO_REF_BPJS'] ?? null,
        //         'JENIS_APLIKASI' => 33,
        //         'STATUS' => 1,
        //         'REF_JADWAL' => $data['REF_JADWAL'] ?? null,
        //         'JAM_PRAKTEK' => $data['JAM_PRAKTEK'] ?? null,
        //     ];
        //     $isAntreanAvailable = $this->reservasiModel
        //         ->where('NORM', $antrean['NORM'])
        //         ->where('TANGGALKUNJUNGAN', $antrean['TANGGALKUNJUNGAN'])
        //         ->where('POLI', $antrean['POLI'])
        //         ->whereIn('STATUS', [1])
        //         ->findAll();
        //     if (count($isAntreanAvailable) > 0) {
        //         $this->reservasiModel->update($isAntreanAvailable[0]['ID'], $antrean);
        //     } else {
        //         $this->reservasiModel->insert($antrean);
        //     }
        //     // $this->reservasiModel->insert($antrean);
        //     $result = $this->addAntreanBPJS($antrean);
        //     if ($result['status']) {
        //         $result['result']['request']['TANGGAL'] = $data['TANGGAL_REF'];
        //         $result['result']['request']['STATUS'] = 1;
        //         $result['result']['request']['RESPONSE'] = $result['result']['message'];
        //         $result['result']['request']['FLAG'] = 0;
        //         $this->logReservasiModel->insert($result['result']['request']);
        //         // $this->checkInAntreanOnsite($antrean['ID']);
        //         return $this->response->setJSON($result['result'])->setStatusCode(200);
        //     }
        //     return $this->response->setJSON($result['result'])->setStatusCode(404);
        // } catch (\Exception $e) {
        //     return $this->response->setJSON([
        //         'status' => false,
        //         'message' => $e->getMessage(),
        //     ])->setStatusCode(500);
        // }
    }
}

<?php

namespace App\Modules\AntreanOnline\Services;

use App\Modules\BPJSVclaim\Services\PesertaService;
use App\Modules\BPJSVclaim\Services\RequestService;
use Config\Database;

class AntreanOnsiteService
{
    protected $db;
    protected $pesertaBpjsService;
    protected $requestService;

    public function __construct()
    {
        $this->db = Database::connect('replikasi');
        $this->pesertaBpjsService = new PesertaService();
        $this->requestService = new RequestService();
        helper('kepesertaan');
    }

    public function getDataPasien($params)
    {
        //params: No. RM / NIK / No. BPJJS
        //harus di pastikan params tersebut jenisnya apa: RM, NIK, BPJS
        $resultJenisIdentitas = jenisIdentitas($params);
        $jenisIdentitas = $resultJenisIdentitas['jenis'];
        $nomorIdentitas = $resultJenisIdentitas['nomor'];

        $builder = $this->db;

        if ($jenisIdentitas == 'nik') {
            $query = $builder->table('master.kartu_identitas_pasien mk')
                ->select('
                    mp.NORM norm, 
                    master.getNamaLengkap(mp.NORM) namaPasien, 
                    mk.NOMOR nik, kaps.NOMOR noBpjs, 
                    kp.NOMOR noHp, 
                    DATE_FORMAT(mp.TANGGAL_LAHIR, "%d/%m/%Y") tanggalLahir, 
                    master.getAlamatPasien(mp.NORM) alamatPasien
                ')
                ->join('master.pasien mp', 'mp.NORM = mk.NORM', 'left')
                ->join('master.kartu_asuransi_pasien kaps', 'kaps.NORM = mk.NORM AND kaps.JENIS = 2', 'left')
                ->join('master.kontak_pasien kp', 'kp.NORM=mp.NORM AND kp.JENIS = 3', 'left')
                ->where('mk.NOMOR', $nomorIdentitas)
                ->where('mk.JENIS', 1)->where('mp.STATUS', 1)
                ->limit(1);
        } else if ($jenisIdentitas == 'noBpjs') {
            $query = $builder->table('master.kartu_asuransi_pasien kaps')
                ->select('
                    mp.NORM norm, 
                    master.getNamaLengkap(mp.NORM) namaPasien, 
                    mk.NOMOR nik, 
                    kaps.NOMOR noBpjs, 
                    kp.NOMOR noHp, 
                    DATE_FORMAT(mp.TANGGAL_LAHIR, "%d/%m/%Y") tanggalLahir, 
                    master.getAlamatPasien(mp.NORM) alamatPasien
                ')
                ->join('master.pasien mp', 'mp.NORM = kaps.NORM', 'left')
                ->join('master.kartu_identitas_pasien mk', 'mk.NORM = mp.NORM AND mk.JENIS = 1', 'left')
                ->join('master.kontak_pasien kp', 'kp.NORM=mp.NORM AND kp.JENIS = 3', 'left')
                ->where('kaps.NOMOR', $nomorIdentitas)
                ->where('kaps.JENIS', 2)->where('mp.STATUS', 1)
                ->limit(1);
        } else if ($jenisIdentitas == 'norm') {
            $query = $builder->table('master.pasien mp')
                ->select('
                    mp.NORM norm,
                    master.getNamaLengkap(mp.NORM) namaPasien,
                    mk.NOMOR nik,
                    kaps.NOMOR noBpjs,
                    kp.NOMOR noHp,
                    DATE_FORMAT(mp.TANGGAL_LAHIR, "%d/%m/%Y") tanggalLahir,
                    master.getAlamatPasien(mp.NORM) alamatPasien
                ')
                ->join('master.kartu_identitas_pasien mk', 'mk.NORM = mp.NORM AND mk.JENIS = 1', 'left')
                ->join('master.kartu_asuransi_pasien kaps', 'kaps.NORM = mp.NORM AND kaps.JENIS = 2', 'left')
                ->join('master.kontak_pasien kp', 'kp.NORM = mp.NORM AND kp.JENIS = 3', 'left')
                ->where('mp.NORM', $nomorIdentitas)
                ->where('mp.STATUS', 1)
                ->limit(1);
        }
        if (isset($query)) {
            $result = $query->get()->getRowArray();
            if (isset($result) && is_array($result) && count($result) > 0) {
                $nik = $result['nik'] ?? null;
                if ($nik) {
                    $kepesertaanResult = $this->pesertaBpjsService->pesertaByNIK($nik);
                    if ($kepesertaanResult['status']) {
                        $result = [...$result, 'kepesertaan' => $kepesertaanResult['data']->peserta ?? null];
                    }
                }
                return ['status' => true, 'data' => $result];
            }
        }
        return ['status' => false];
    }

    public function getHistoryPendaftaran($norm)
    {
        $builder = $this->db->table('pendaftaran.pendaftaran pp')
            ->select('pp.NOMOR nomorPendaftaran, DATE_FORMAT(pp.TANGGAL, "%d/%m/%Y") tanggal, mr.DESKRIPSI namaRuangan, master.getNamaLengkapPegawai(md.NIP) namaDokter, ref.DESKRIPSI namaPenjamin, IFNULL(penjamin.NOMOR, "-") nomorSep')
            ->join('pendaftaran.tujuan_pasien tp', 'tp.NOPEN = pp.NOMOR', 'left')
            ->join('pendaftaran.penjamin penjamin', 'penjamin.NOPEN = pp.NOMOR', 'left')
            ->join('master.dokter md', 'md.ID = tp.DOKTER', 'left')
            ->join('master.pasien mp', 'mp.NORM = pp.NORM', 'left')
            ->join('master.ruangan mr', 'mr.ID = tp.RUANGAN', 'left')
            ->join('master.referensi ref', 'ref.ID = penjamin.JENIS AND ref.JENIS = 10', 'left')
            ->where('pp.NORM', $norm)
            ->where('pp.STATUS !=', 0)
            ->where('tp.STATUS !=', 0)
            ->orderBy('pp.TANGGAL', 'DESC')
            ->limit(10);
        $result = $builder->get()->getResultArray();
        if (count($result) > 0) return ['status' => true, 'data' => $result];
        return ['status' => false];
    }

    public function getReservasi($norm)
    {
        /*
            kodeBooking ✅
            tanggalKunjungn ✅
            tanggalReservasi ✅
            dokterTujuan ✅ 
            namaRuangan ✅
            antrean ✅
            sumberReservasi = 1 (WEB/MOBILE), 2 (MJKN), 3 (SIMRS), 33 (ONSITE) ✅
            statusReservasi = belum checkin, checkin, batal ✅
        */
        $builder = $this->db->table('regonline.reservasi res')
            ->select('res.ID kodeBooking, res.TANGGALKUNJUNGAN tanggalKunjungan, res.TANGGAL_REF tanggalReservasi, 
            mr.DESKRIPSI namaRuangan, master.getNamaLengkapPegawai(md.NIP) dokterTujuan, res.NO antrean, res.NO_REF_BPJS noRef,
            (
                CASE res.JENIS_APLIKASI
                    WHEN 1 THEN "WEB/MOBILE"
                    WHEN 2 THEN "MJKN"
                    WHEN 3 THEN "SIMRS"
                    WHEN 33 THEN "ONSITE"
                    ELSE "Tidak diketahui"
                END) AS sumberReservasi,
            (
                CASE
                    WHEN (res.STATUS = 1 OR res.STATUS = 99 OR res.STATUS = 2) AND res.WAKTU_CHECK_IN IS NULL THEN "Belum Check In"
                    WHEN res.STATUS != 0 AND res.WAKTU_CHECK_IN IS NOT NULL THEN "Sudah Check In"
                    WHEN res.STATUS = 0 THEN "Batal"
                    ELSE "Tidak diketahui"
                END
            ) as statusReservasi
            ')
            ->join('regonline.jadwal_dokter_hfis jd', 'jd.KD_DOKTER = res.DOKTER', 'left')
            ->join('penjamin_rs.dpjp dpjp', 'dpjp.DPJP_PENJAMIN = jd.KD_DOKTER', 'left')
            ->join('master.dokter md', 'md.ID = dpjp.DPJP_RS', 'left')
            ->join('master.ruangan mr', 'mr.ID = res.POLI', 'left')
            ->where('res.NORM', $norm)
            ->where('dpjp.STATUS', 1)
            ->orderBy('res.TANGGALKUNJUNGAN', 'DESC')
            ->groupBy('res.ID')
            ->limit(10);
        $result = $builder->get()->getResultArray();
        if (count($result) > 0) return ['status' => true, 'data' => $result];
        return ['status' => false];
    }

    public function getJadwalDokter($params)
    {
        $tanggal = $params['tanggal'];
        $kodePoli = $params['kodePoli'];
        $endPoint = env("SIMGOS_URL") . 'apps/RegOnline/api/jadwaldokter/?TANGGAL=' . $tanggal . '&POLI=' . $kodePoli;
        $request = $this->requestService->sendServiceSIMRSRequest('GET', $endPoint);
        if ($request['status']) {
            $requestFormatted = $this->setFormatJadwalDokter($request['data']);
            return [
                'status' => true,
                'message' => 'Jadwal dokter ditemukan',
                'data' => $requestFormatted
            ];
        }

        return [
            'status' => false,
            'message' => 'Jadwal dokter tidak ditemukan'
        ];
    }

    private function setFormatJadwalDokter($data)
    {
        $result = [];
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                if ($value['STATUS'] == 1) {
                    $masterDokter = $this->getPenjamin($value['KD_DOKTER']);
                    $result[$key] = [
                        'ID' => $value['ID'] ?? null,
                        'SMF' => $masterDokter[0]['SMF'] ?? null,
                        'ID_DOKTER' => $masterDokter[0]['DPJP_RS'] ?? null,
                        'KD_DOKTER' => $value['KD_DOKTER'] ?? null,
                        'NM_DOKTER' => $value['NM_DOKTER'] ?? null,
                        'KD_SUB_SPESIALIS' => $value['KD_SUB_SPESIALIS'] ?? null,
                        'KD_POLI' => $value['KD_SUB_SPESIALIS'] ?? null,
                        'JAM_LAYANAN' => $value['JAM'] ?? null,
                        'JAM_MULAI' => $value['JAM_MULAI'] ?? null,
                        'JAM_SELESAI' => $value['JAM_SELESAI'] ?? null,
                        'JAM_MULAI' => $value['JAM_MULAI'] ?? null,
                        'STATUS' => $value['STATUS'] ?? null,
                    ];
                }
            }
        }

        return $result;
    }

    private function getPenjamin($kodeDokter)
    {
        $data = $this->db->table('penjamin_rs.dpjp')
            ->select('dpjp.DPJP_RS, smf.SMF')
            ->join('master.dokter_smf smf', 'smf.DOKTER = dpjp.DPJP_RS', 'left')
            ->where('dpjp.DPJP_PENJAMIN', $kodeDokter)
            ->where('dpjp.STATUS', 1)
            ->where('dpjp.PENJAMIN', 2)
            ->where('smf.STATUS', 1)
            ->get()->getRowArray();
        return $data;
    }

    public function getPoliklinik()
    {
        $builder = $this->db->table('master.ruangan');
        $builder->select('ruangan.ID idPoli, ruangan.DESKRIPSI namaPoli, penjamin_ruangan.RUANGAN_PENJAMIN kodePoli');
        $builder->join('master.penjamin_ruangan', 'ruangan.ID = penjamin_ruangan.RUANGAN_RS', 'left');
        $builder->where('ruangan.STATUS', '1')->where('ruangan.JENIS_KUNJUNGAN', 1)->where('ruangan.REF_ID', 0)->where('penjamin_ruangan.STATUS', '1');
        $builder->orderBy('ruangan.DESKRIPSI', 'ASC');
        $query = $builder->get();
        if ($query->getNumRows() > 0) return ['status' => true, 'data' => $query->getResultArray()];
        return ['status' => false];
    }

    // public function updateLogReservasi($nomorBooking)
    // {
    //     $builder = $this->db->connect('');
    //     $builder = $this->db->table('log_reservasi');
    //     $builder->set([
    //         'STATUS' => 99,
    //         'RESPONSE' => 'DIBATALKAN',
    //     ]);
    //     $builder->where('kodebooking', $nomorBooking);
    //     $builder->update();
    // }

    // public function generateKodeBooking($tanggal)
    // {
    //     $query = $this->db->query("SELECT generatorReservasi('" . $tanggal . "') as nomorBooking");
    //     $result = $query->getResult();
    //     $nomorBooking = "";
    //     if (count($result) > 0) {
    //         $nomorBooking = $result[0]->nomorBooking;
    //     }
    //     return $nomorBooking;
    // }

    // public function generateNomorAntrean($pos, $tanggal, $caraBayar)
    // {
    //     $query = $this->db->query("SELECT getNomorAntrian('" . $pos . "','" . $tanggal . "'," . $caraBayar . " ) as nomorAntrean");
    //     $result = $query->getResult();
    //     $nomorAntrean = "";
    //     if (count($result) > 0) {
    //         $nomorAntrean = $result[0]->nomorAntrean;
    //     }
    //     return $nomorAntrean;
    // }

    // public function generateNomorAntreanPoli($idRuangan, $tanggal)
    // {
    //     $query = $this->db->query("SELECT generateNoAntrianPoli('" . $idRuangan . "','" . $tanggal . "') as nomorAntreanPoli");
    //     $result = $query->getResult();
    //     $nomorAntreanPoli = "";
    //     if (count($result) > 0) {
    //         $nomorAntreanPoli = $result[0]->nomorAntreanPoli;
    //     }
    //     return $nomorAntreanPoli;
    // }

    // public function generateNomorAntreanBPJS($tanggal, $kodePoli, $kodeDokter, $jamLayanan)
    // {
    //     $query = $this->db->query("SELECT generateNoAntrianBpjs('" . $tanggal . "','" . $kodePoli . "','" . $kodeDokter . "','" . $jamLayanan . "') as nomorAntreanBPJS");
    //     $result = $query->getResult();
    //     $nomorAntreanBPJS = "";
    //     if (count($result) > 0) {
    //         $nomorAntreanBPJS = $result[0]->nomorAntreanBPJS;
    //     }
    //     return $nomorAntreanBPJS;
    // }

    // public function generateJamAntrean($nomorAntrean, $pos, $idRuangan, $tanggal)
    // {
    //     $query = $this->db->query("SELECT getJamAntrian(" . $nomorAntrean . ",'" . $pos . "','" . $idRuangan . "','" . $tanggal . "') as jamAntrean");
    //     $result = $query->getResult();
    //     $jamAntrean = "";
    //     if (count($result) > 0) {
    //         $jamAntrean = $result[0]->jamAntrean;
    //     }
    //     return $jamAntrean;
    // }

    // public function generateJamPelayanan($nomorAntrean, $pos, $idRuangan, $tanggal)
    // {
    //     $query = $this->db->query("SELECT getJamPelayanan(" . $nomorAntrean . ",'" . $pos . "','" . $idRuangan . "','" . $tanggal . "') as jamPelayanan");
    //     $result = $query->getResult();
    //     $jamPelayanan = "";
    //     if (count($result) > 0) {
    //         $jamPelayanan = $result[0]->jamPelayanan;
    //     }
    //     return $jamPelayanan;
    // }

    // public function getKuotaAntrian($idRefJadwal, $kodeDokter)
    // {
    //     $builder = $this->db->table('regonline.jadwal_dokter_hfis');
    //     $builder->where('ID', $idRefJadwal)->where('KD_DOKTER', $kodeDokter);
    //     $query = $builder->get()->getResultArray();
    //     $kuota = [];
    //     if (count($query) > 0) {
    //         $kuota = [
    //             'KUOTA_JKN' => $query[0]['KOUTA_JKN'],
    //             'KUOTA_NON_JKN' => $query[0]['KOUTA_NON_JKN'],
    //         ];
    //     }

    //     return $kuota;
    // }

    // public function getReservasiPasien($params = [], $tanggalKunjungan)
    // {
    //     $reservasiPasien = $this->where($params['key'], $params['value'])
    //         ->where('TANGGALKUNJUNGAN', $tanggalKunjungan)
    //         ->where('JENIS_APLIKASI', 2)
    //         ->findAll();
    //     return $reservasiPasien;
    // }
}

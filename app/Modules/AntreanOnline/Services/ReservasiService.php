<?php

namespace App\Modules\AntreanOnline\Services;

use App\Modules\AntreanOnline\Models\ReservasiModel;
use App\Modules\BPJSAntrol\Services\AntreanService;
use Config\Database;

class ReservasiService
{
    protected $reservasiModel;
    protected $antreanService;
    protected $db;

    public function __construct()
    {
        $this->reservasiModel = new ReservasiModel();
        $this->antreanService = new AntreanService();
        $this->db = Database::connect('regonline');
    }

    public function getData($params)
    {
        $builder = $this->db->table('regonline.reservasi rr');
        $builder->select('rr.id kodeBooking, rr.NORM norm, rr.NIK nik, rr.NAMA namaPasien, rr.TANGGALKUNJUNGAN tanggalKunjungan, mr.DESKRIPSI namaRuangan, rr.NO_REF_BPJS noRefBPJS, master.getNamaLengkapPegawai(md.NIP) dokter, rr.STATUS status, rr.WAKTU_CHECK_IN checkIn');
        $builder->join('master.ruangan mr', 'mr.ID = rr.POLI', 'left');
        $builder->join('penjamin_rs.dpjp dp', 'dp.DPJP_PENJAMIN = rr.DOKTER', 'left');
        $builder->join('master.dokter md', 'md.ID = dp.DPJP_RS', 'left');
        $builder->where('mr.STATUS !=', 0);
        $builder->where('dp.STATUS !=', 0);
        if (isset($params['startDate']) && isset($params['endDate'])) {
            $builder->where('rr.TANGGALKUNJUNGAN BETWEEN "' . $params['startDate'] . '" AND "' . $params['endDate'] . '"');
        }
        if (isset($params['status']) && $params['status'] != '') {
            switch ($params['status']) {
                case 1:
                    // Belum check in, kodebooking tidak batal
                    $builder->where('rr.STATUS !=', 0)
                        ->where('rr.WAKTU_CHECK_IN IS NULL', null, false);
                    break;

                case 2:
                    // Sudah check in
                    $builder->where('rr.STATUS !=', 0)
                        ->where('rr.WAKTU_CHECK_IN IS NOT NULL', null, false);
                    break;

                default:
                    // Batal
                    $builder->where('rr.STATUS', 0);
                    break;
            }
        }
        if (isset($params['ruangan']) && $params['ruangan'] != '') {
            $builder->where('rr.POLI', $params['ruangan']);
        }
        if (isset($params['dokter']) && $params['dokter'] != '') {
            $builder->where('rr.DOKTER', $params['dokter']);
        }

        if (!empty($params['search']) && $params['search'] != '') {
            $builder->like('rr.NAMA', $params['search']);
        }

        if (!empty($params['sortBy']) && !empty($params['sortDir'])) {
            $builder->orderBy($params['sortBy'], $params['sortDir']);
        }

        if (!empty($params['caraBayar']) && $params['caraBayar'] != '') {
            $builder->where('rr.CARABAYAR', $params['caraBayar']);
        }

        $perPage = $params['limit'] ?? getenv('DEFAULT_PAGINATE_LIMIT');
        $page = $params['page'] ?? 1;
        $offset = ($page - 1) * $perPage;

        $total = $builder->countAllResults(false);
        $data = $builder->limit($perPage, $offset)->get()->getResult();
        if (count($data) < 1) return ['status' => false];
        return [
            'status' => true,
            'data' => [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $perPage,
            ]
        ];
    }

    public function getSummaryReservasi($params)
    {
        // get summary semua reservasi dengan filter dokter dan tanggal kunjungan
        // get summary reservasi sudah check in
        // get summary reservasi belum check in
        // get summary reservasi batal

        $startDate = (isset($params['startDate']) && $params['startDate'] != '') ? $params['startDate'] : date('Y-m-d');
        $endDate = (isset($params['endDate']) && $params['endDate'] != '') ? $params['endDate'] : date('Y-m-d');
        $dokter = (isset($params['dokter']) && $params['dokter'] != '') ? $params['dokter'] : null;
        $caraBayar = (isset($params['caraBayar']) && $params['caraBayar'] != '') ? $params['caraBayar'] : 2;

        $builder = $this->db->table('regonline.reservasi rr');
        $builder->select('
        (
            SELECT COUNT(ID) 
            FROM regonline.reservasi 
            WHERE TANGGALKUNJUNGAN BETWEEN "' . $startDate . '" AND "' . $endDate . '" AND DOKTER = ' . $dokter . ' AND STATUS != 0 AND CARABAYAR = ' . $caraBayar . '
        ) AS totalReservasi,
        (
            SELECT IFNULL(SUM(DISTINCT(jdh.KAPASITAS)), 0)
            FROM regonline.reservasi rr
            LEFT JOIN regonline.jadwal_dokter_hfis jdh ON jdh.ID = rr.REF_JADWAL
            WHERE rr.TANGGALKUNJUNGAN BETWEEN "' . $startDate . '" AND "' . $endDate . '" 
            AND rr.DOKTER = ' . $dokter . ' 
            AND rr.STATUS != 0 
            AND rr.CARABAYAR = ' . $caraBayar . ' 
            AND jdh.STATUS = 1
        ) AS totalKapasitas,
        (
            SELECT COUNT(ID) 
            FROM regonline.reservasi 
            WHERE TANGGALKUNJUNGAN BETWEEN "' . $startDate . '" AND "' . $endDate . '" AND DOKTER = ' . $dokter . ' AND STATUS != 0 AND WAKTU_CHECK_IN IS NOT NULL AND CARABAYAR = ' . $caraBayar . '
        ) AS totalCheckIn,
        (
            SELECT COUNT(ID) 
            FROM regonline.reservasi 
            WHERE TANGGALKUNJUNGAN BETWEEN "' . $startDate . '" AND "' . $endDate . '" AND DOKTER = ' . $dokter . ' AND STATUS != 0 AND WAKTU_CHECK_IN IS NULL AND CARABAYAR = ' . $caraBayar . '
        ) AS totalBelumCheckIn,
        (
            SELECT COUNT(ID) 
            FROM regonline.reservasi 
            WHERE TANGGALKUNJUNGAN BETWEEN "' . $startDate . '" AND "' . $endDate . '" AND DOKTER = ' . $dokter . ' AND STATUS = 0 AND CARABAYAR = ' . $caraBayar . '
        ) AS totalBatal
        ');

        $query = $builder->get()->getRowArray();
        if (count($query) > 1) {
            return ['status' => true, 'data' => $query];
        }

        return ['status' => false];
    }

    public function batalkanReservasi($params)
    {

        if (isset($params['onBPJS']) && isset($params['onLocal'])) {
            $batalkanDiBPJS = $this->antreanService->batalAntrean(['kodebooking' => $params['kodeBooking'], 'keterangan' => 'Terjadi perubahan jadwal atau dokter tidak praktik']);
            if ($batalkanDiBPJS['status']) {
                $this->db->table('regonline.reservasi')->where('ID', $params['kodeBooking'])->update(['STATUS' => 0, 'WAKTU_CHECK_IN' => null]);
                return ['status' => true];
            }
            return $batalkanDiBPJS;
        } else if (isset($params['onBPJS'])) {
            $batalkanDiBPJS = $this->antreanService->batalAntrean(['kodebooking' => $params['kodeBooking'], 'keterangan' => 'Terjadi perubahan jadwal atau dokter tidak praktik']);
            if ($batalkanDiBPJS['status']) {
                return ['status' => true];
            }
            return $batalkanDiBPJS;
        } else if (isset($params['onLocal'])) {
            $this->db->table('regonline.reservasi')->where('ID', $params['kodeBooking'])->update(['STATUS' => 0, 'WAKTU_CHECK_IN' => null]);
            return ['status' => true];
        }
    }

    public function batalkanReservasiMassal($kodebookings)
    {
        $result = [
            'berhasil' => [],
            'gagal' => []
        ];
        foreach ($kodebookings as $kodebooking) {
            $resultBatalDiBPJS = $this->antreanService->batalAntrean(['kodebooking' => $kodebooking, 'keterangan' => 'Terjadi perubahan jadwal atau dokter tidak praktik']);
            if ($resultBatalDiBPJS['status']) {
                $this->db->table('regonline.reservasi')->where('ID', $kodebooking)->update(['STATUS' => 0, 'WAKTU_CHECK_IN' => null]);
                $result['berhasil'][] = $kodebooking;
            } else {
                $result['gagal'][] = $kodebooking;
            }
        }

        return ['status' => true, 'data' => $result];
    }
}

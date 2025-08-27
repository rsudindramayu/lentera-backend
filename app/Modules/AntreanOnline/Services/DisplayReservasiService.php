<?php

namespace App\Modules\AntreanOnline\Services;

use CodeIgniter\Database\SQLite3\Table;
use Config\Database;
use DateTime;

class DisplayReservasiService
{
    protected $db;
    public function __construct()
    {
        $this->db = Database::connect("replikasi");
    }

    public function getDataDisplayReservasi($params)
    {
        $day = new DateTime($params['tanggalKunjungan']);
        $dayNumber = $day->format('w');
        $convertedDay = $dayNumber == 0 ? 7 : $dayNumber;
        $params['convertedDay'] = $convertedDay;

        $polikliniks = $this->getPolikliniks($params);
        $dataReservasiDokter = array();

        unset($params['convertedDay']);

        if (!empty($polikliniks)) {
            foreach ($polikliniks as $poliklinik) {
                $poliklinik['tanggalKunjungan'] = $params['tanggalKunjungan'];
                $result = $this->getInformasiReservasi($poliklinik) ?? [];

                if (!empty($result)) $dataReservasiDokter = array_merge($dataReservasiDokter, $result);
            }
            return ['status' => true, 'data' => $dataReservasiDokter];
        }

        return ['status' => false, 'message' => 'Data reservasi tidak ditemukan!'];
    }

    public function getPolikliniks($params)
    {
        $builder = $this->db->table('lentera-antrol.jadwal_poliklinik jp')
            ->select('jp.*, mr.DESKRIPSI deskripsiPoli')
            ->join('master.ruangan mr', 'mr.ID = jp.ruangan_id', 'left')
            ->where('mr.STATUS !=', 0)
            ->where('jp.hari', $params['convertedDay'])->where('jp.status', 1);
        if (isset($params['kodeDokter']) && $params['kodeDokter'] != '') {
            $builder->where('jp.dokter_id', $params['kodeDokter']);
        }
        $builder->orderBy('mr.DESKRIPSI', 'ASC');
        return $builder->get()->getResultArray();
    }

    public function getInformasiReservasi($params)
    {
        /*
        Input dari fungsi ini:
        [
            'tanggalKunjungan'
        ];
        Output dari fungsi ini list data:
        [
            'namaPoliklinik', ✅
            'namaDokter', ✅
            'kuotaDokter', ✅
            'totalReservasiBPJS', ✅
            'totalPasienBPJSTerdaftar', ✅
            'totalPasienBPJSRujin', 🔛 masih perlu di analisa
            'totalPasienNonBPJS', ✅
            'statusPendaftaran' ✅
        ];  
        */

        $tanggalKunjungan = $params['tanggalKunjungan'] ?? date('Y-m-d');
        $ruanganId = $params['ruangan_id'];

        $builder = $this->db->table('regonline.jadwal_dokter_hfis');
        $builder = $builder->select("
            jadwal_dokter_hfis.KD_DOKTER AS kodeDokter, 
            dpjp.DPJP_RS AS idDokter, 
            master.getNamaLengkapPegawai(md.NIP) AS namaDokter, 
            jadwal_dokter_hfis.JAM AS jamLayanan,
            jadwal_dokter_hfis.KOUTA_JKN AS kuotaDokter,
            (
                SELECT COUNT(*)
                FROM regonline.reservasi r
                WHERE r.TANGGALKUNJUNGAN = '" . $tanggalKunjungan . "'
                AND r.DOKTER = jadwal_dokter_hfis.KD_DOKTER
                AND r.CARABAYAR = 2
                AND r.STATUS != 0
            ) AS totalReservasiBPJS,
              (
                SELECT COUNT(*)
                FROM pendaftaran.pendaftaran pp
                LEFT JOIN pendaftaran.tujuan_pasien tp ON tp.NOPEN = pp.NOMOR
                LEFT JOIN pendaftaran.penjamin ppen ON ppen.NOPEN = pp.NOMOR
                WHERE pp.TANGGAL LIKE '" . $tanggalKunjungan . "%'
                AND pp.STATUS != 0 AND tp.STATUS != 0 
                AND tp.RUANGAN = " . $ruanganId . " AND tp.DOKTER = md.ID
                AND ppen.JENIS = 2
            ) AS totalPasienBPJSTerdaftar,
            /*
             (
                SELECT COUNT(pp.NOMOR) FROM pendaftaran.pendaftaran pp
                LEFT JOIN pendaftaran.penjamin ppen ON ppen.NOPEN=pp.NOMOR
                LEFT JOIN pendaftaran.tujuan_pasien tujuan ON tujuan.NOPEN=pp.NOMOR
                LEFT JOIN bpjs.kunjungan bpjs ON bpjs.noSEP=ppen.NOMOR
                WHERE pp.TANGGAL LIKE '" . $tanggalKunjungan . "%' 
                AND pp.STATUS != 0 AND tujuan.STATUS != 0 AND bpjs.status != 0 AND bpjs.batalSEP != 1
                AND tujuan.RUANGAN=" . $ruanganId . " AND tujuan.DOKTER=md.ID AND ppen.JENIS=2 AND bpjs.asalRujukan IS NULL
            ) AS totalPasienBPJSRujin,
            */
            (
                SELECT COUNT(*)
                FROM pendaftaran.pendaftaran pp
                LEFT JOIN pendaftaran.tujuan_pasien tp ON tp.NOPEN = pp.NOMOR
                LEFT JOIN pendaftaran.penjamin ppen ON ppen.NOPEN = pp.NOMOR
                WHERE pp.TANGGAL LIKE '" . $tanggalKunjungan . "%'
                AND pp.STATUS != 0 AND tp.STATUS != 0 
                AND tp.RUANGAN = " . $ruanganId . " AND tp.DOKTER = md.ID
                AND ppen.JENIS != 2
            ) AS totalPasienNonBPJS,
                COALESCE((
                SELECT sp.STATUS 
                FROM `lentera-antrol`.status_pendaftaran sp 
                WHERE sp.TANGGAL = '" . $tanggalKunjungan . "'
                AND sp.DOKTER = jadwal_dokter_hfis.KD_DOKTER
                LIMIT 1
                ), 'buka') AS statusPendaftaran
            ")
            ->join('penjamin_rs.dpjp dpjp', 'dpjp.DPJP_PENJAMIN = jadwal_dokter_hfis.KD_DOKTER', 'left')
            ->join('master.dokter md', 'md.ID = dpjp.DPJP_RS', 'left')
            ->where('jadwal_dokter_hfis.KD_POLI', $params['kodePoli'])
            ->where('jadwal_dokter_hfis.HARI', $params['hari'])
            ->where('jadwal_dokter_hfis.STATUS', 1)
            ->where('dpjp.STATUS', 1)
            ->where("NOT EXISTS (
                SELECT 1 
                FROM regonline.dokter_libur dl 
                WHERE dl.DOKTER = jadwal_dokter_hfis.KD_DOKTER
                AND dl.TANGGAL = '" . $tanggalKunjungan . "'
                AND dl.status = 1
            )", null, false);

        if ($params['isMulti'] == "1" && isset($params['dokter_id'])) {
            $builder->where('jadwal_dokter_hfis.KD_DOKTER', $params['dokter_id']);
        }

        $query = $builder->get()->getResultArray();

        return array_map(function ($row) use ($params) {
            return [
                'tanggalKunjungan' => $params['tanggalKunjungan'],
                'ruanganId' => $params['ruangan_id'],
                'kodePoli' => $params['kodePoli'],
                'namaRuangan' => $params['deskripsiPoli'],
                'idDokter' => $row['idDokter'],
                'kodeDokter' => $row['kodeDokter'],
                'namaDokter' => $row['namaDokter'],
                'kuotaDokter' => $row['kuotaDokter'],
                'totalReservasiBPJS' => $row['totalReservasiBPJS'],
                'totalPasienBPJSTerdaftar' => $row['totalPasienBPJSTerdaftar'],
                'totalPasienBPJSRujin' => "0",
                'totalPasienNonBPJS' => $row['totalPasienNonBPJS'],
                'statusPendaftaran' => $row['statusPendaftaran'],
            ];
        }, $query);
    }

    public function getPengunjung($params)
    {
        /*
            params: tanggalKunjungan, dokter, ruangan, caraBayar = 2 (bpjs) != 2 (non bpjs) dan search
        */

        $tanggalKunjungan = $params['tanggalKunjungan'];
        $dokterId = $params['dokter'];
        $ruanganId = $params['ruangan'];
        $caraBayar = $params['caraBayar'];

        $builder = $this->db->table('pendaftaran.pendaftaran pp');
        $builder->select('pp.NOMOR nomorPendaftaran, pp.NORM, master.getNamaLengkap(mp.NORM) namaPasien, pp.TANGGAL tanggalPendaftaran, mr.DESKRIPSI namaRuangan, master.getNamaLengkapPegawai(md.NIP) namaDokter, ref.DESKRIPSI namaPenjamin');
        $builder->join('pendaftaran.tujuan_pasien tp', 'tp.NOPEN = pp.NOMOR', 'left');
        $builder->join('pendaftaran.penjamin penjamin', 'penjamin.NOPEN = pp.NOMOR', 'left');
        $builder->join('master.dokter md', 'md.ID = tp.DOKTER', 'left');
        $builder->join('master.pasien mp', 'mp.NORM = pp.NORM', 'left');
        $builder->join('master.ruangan mr', 'mr.ID = tp.RUANGAN', 'left');
        $builder->join('master.referensi ref', 'ref.ID = penjamin.JENIS AND ref.JENIS = 10', 'left');
        $builder->where('pp.TANGGAL LIKE', $tanggalKunjungan . '%');
        $builder->where('pp.STATUS !=', 0);
        $builder->where('tp.STATUS !=', 0);
        $builder->where('tp.DOKTER', $dokterId);
        $builder->where('tp.RUANGAN', $ruanganId);
        if ($caraBayar != 2) $builder->where('penjamin.JENIS !=', 2);
        else $builder->where('penjamin.JENIS', $caraBayar);

        //searchValue

        if (isset($params['search']) && $params['search'] != '') {
            $builder->where('mp.NAMA LIKE', '%' . $params['search'] . '%');
        }

        //setpagination
        $perPage = $params['limit'] ?? getenv('DEFAULT_PAGINATE_LIMIT');
        $page = $params['page'] ?? 1;
        $offset = ($page - 1) * $perPage;
        $total = $builder->countAllResults(false);
        $builder->orderBy('pp.NOMOR', 'ASC');
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
}

<?php

namespace App\Modules\BPJSVclaim\Services;

class SEPService
{
    protected $baseUrl;
    protected $requestService;

    public function __construct()
    {
        $this->baseUrl = env('VCLAIM_URL') . 'Sep';
        $this->requestService = new RequestService();
    }
    public function listDataUpdateTanggalPulang($params)
    {
        //params: bulan, tahun dan limit
        //params limit opsional
        //apabila params limit dikosongkan akan menampilkan semua data pada bulan dan tahun pilihan

        $bulan = $params['bulan'];
        $tahun = $params['tahun'] ?? date('Y');
        $endPoint = $this->baseUrl . '/updtglplg/list/bulan/' . $bulan . '/tahun/' . $tahun . '/';
        if (isset($params['limit'])) $endPoint . '' . $params['limit'];
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }

    public function cariSEP($sep)
    {
        $endPoint = $this->baseUrl . '/' . $sep;
        $result = $this->requestService->sendRequest('GET', $endPoint);
        return $result;
    }

    public function insertSEP($params)
    {
        $data = [
            "t_sep" => [
                "noKartu" => "0001009114514",
                "tglSep" => "2024-12-20",
                "ppkPelayanan" => "1021R001",
                "jnsPelayanan" => "2",
                "klsRawat" => [
                    "klsRawatHak" => "3",
                    "klsRawatNaik" => "",
                    "pembiayaan" => "",
                    "penanggungJawab" => ""
                ],
                "noMR" => "2261157",
                "rujukan" => [
                    "asalRujukan" => "1",
                    "tglRujukan" => "2024-11-28",
                    "noRujukan" => "102114011124Y001932",
                    "ppkRujukan" => "10211401"
                ],
                "catatan" => "",
                "diagAwal" => "H25",
                "poli" => [
                    "tujuan" => "MAT",
                    "eksekutif" => "0"
                ],
                "cob" => [
                    "cob" => "0"
                ],
                "katarak" => [
                    "katarak" => "0"
                ],
                "jaminan" => [
                    "lakaLantas" => "0",
                    "noLP" => "",
                    "penjamin" => [
                        "tglKejadian" => "",
                        "keterangan" => "",
                        "suplesi" => [
                            "suplesi" => "0",
                            "noSepSuplesi" => "",
                            "lokasiLaka" => [
                                "kdPropinsi" => "",
                                "kdKabupaten" => "",
                                "kdKecamatan" => ""
                            ]
                        ]
                    ]
                ],
                "tujuanKunj" => "2",
                "flagProcedure" => "",
                "kdPenunjang" => "",
                "assesmentPel" => "5",
                "skdp" => [
                    "noSurat" => "1021R0011224K002478",
                    "kodeDPJP" => "8224"
                ],
                "dpjpLayan" => "8224",
                "noTelp" => "080000000000",
                "user" => "Webservice"
            ]
        ];

        $endPoint = $this->baseUrl . '/2.0/insert';
        $setBody = ['request' => $data];
        $request = $this->requestService->sendRequest('POST', $endPoint, json_encode($setBody));
        return $request;
    }
}

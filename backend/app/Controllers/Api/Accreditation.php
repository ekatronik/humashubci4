<?php

namespace App\Controllers\Api;

use App\Models\CampusAccreditationModel;
use App\Models\FacultyModel;
use App\Models\StudyProgramModel;

/**
 * API Controller — Manajemen Akreditasi Prodi & Kampus
 */
class Accreditation extends BaseApiController
{
    /**
     * GET /api/admin/accreditation/stats
     */
    public function stats()
    {
        $facultyModel = new FacultyModel();
        $prodiModel   = new StudyProgramModel();
        $campusModel  = new CampusAccreditationModel();

        $totalFaculties = $facultyModel->countAllResults();
        $totalProdis    = $prodiModel->countAllResults();
        $totalUnggul    = $prodiModel->where('akreditasi_sekarang', 'Unggul')->countAllResults();

        $campus = $campusModel->first();
        if (!$campus) {
            $campusModel->insert(['nama_institusi' => 'UIN Ar-Raniry Banda Aceh']);
            $campus = $campusModel->first();
        }

        $recentProdis = $prodiModel->select('study_programs.*, faculties.nama_fakultas, faculties.singkatan as singkatan_fakultas')
            ->join('faculties', 'faculties.id = study_programs.faculty_id')
            ->orderBy('study_programs.id', 'DESC')
            ->limit(5)
            ->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'total_faculties'    => $totalFaculties,
                'total_prodis'       => $totalProdis,
                'total_unggul'       => $totalUnggul,
                'campus'             => $campus,
                'recent_prodis'      => $recentProdis
            ]
        ]);
    }

    /**
     * GET /api/admin/accreditation/campus
     */
    public function getCampus()
    {
        $campusModel = new CampusAccreditationModel();
        $campus = $campusModel->first();
        if (!$campus) {
            $campusModel->insert(['nama_institusi' => 'UIN Ar-Raniry Banda Aceh']);
            $campus = $campusModel->first();
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $campus
        ]);
    }

    /**
     * POST/PUT /api/admin/accreditation/campus
     */
    public function updateCampus()
    {
        $campusModel = new CampusAccreditationModel();
        $campus = $campusModel->first();
        if (!$campus) {
            $campusModel->insert(['nama_institusi' => 'UIN Ar-Raniry Banda Aceh']);
            $campus = $campusModel->first();
        }

        $id = $campus['id'];

        $namaInstitusi = trim($this->request->getPost('nama_institusi') ?? $campus['nama_institusi']);
        $akreditasiSekarang = $this->request->getPost('akreditasi_sekarang') ?: null;
        $masaBerlaku = $this->request->getPost('masa_berlaku') ?: null;

        $akreditasiP1 = $this->request->getPost('akreditasi_periode_1') ?: null;
        $masaBerlakuP1 = $this->request->getPost('masa_berlaku_periode_1') ?: null;

        $akreditasiP2 = $this->request->getPost('akreditasi_periode_2') ?: null;
        $masaBerlakuP2 = $this->request->getPost('masa_berlaku_periode_2') ?: null;

        $clearCert = $this->request->getPost('clear_cert') === '1';
        $clearCertP1 = $this->request->getPost('clear_cert_periode_1') === '1';
        $clearCertP2 = $this->request->getPost('clear_cert_periode_2') === '1';

        $data = [
            'nama_institusi'       => $namaInstitusi,
            'akreditasi_sekarang'  => $akreditasiSekarang,
            'masa_berlaku'         => $masaBerlaku,
            'akreditasi_periode_1' => $akreditasiP1,
            'masa_berlaku_periode_1' => $masaBerlakuP1,
            'akreditasi_periode_2' => $akreditasiP2,
            'masa_berlaku_periode_2' => $masaBerlakuP2,
        ];

        $data['sertifikat_berlaku']   = $this->request->getPost('sertifikat_berlaku') ?: null;
        $data['sertifikat_periode_1'] = $this->request->getPost('sertifikat_periode_1') ?: null;
        $data['sertifikat_periode_2'] = $this->request->getPost('sertifikat_periode_2') ?: null;

        $campusModel->update($id, $data);

        $this->logActivity("Memperbarui akreditasi institusi", 'Akreditasi', $id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Akreditasi institusi berhasil diperbarui.',
            'data'    => $campusModel->find($id)
        ]);
    }

    /**
     * GET /api/admin/accreditation/faculties
     */
    public function listFaculties()
    {
        $facultyModel = new FacultyModel();
        $faculties = $facultyModel->orderBy('nama_fakultas', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $faculties
        ]);
    }

    /**
     * POST /api/admin/accreditation/faculties
     */
    public function createFaculty()
    {
        $input = $this->request->getJSON(true) ?? [];
        if (empty($input)) {
            $input = $this->request->getPost() ?? [];
        }

        $namaFakultas = trim($input['nama_fakultas'] ?? '');
        $singkatan    = trim($input['singkatan'] ?? '');
        $website      = trim($input['website'] ?? '');

        if (empty($namaFakultas) || empty($singkatan)) {
            return $this->respondWithError('Nama fakultas dan singkatan wajib diisi.', 400);
        }

        $facultyModel = new FacultyModel();

        // Check duplicate abbreviation
        if ($facultyModel->where('singkatan', $singkatan)->first()) {
            return $this->respondWithError('Fakultas dengan singkatan ini sudah ada.', 409);
        }

        $id = $facultyModel->insert([
            'nama_fakultas' => $namaFakultas,
            'singkatan'     => $singkatan,
            'website'       => $website ?: null,
        ], true);

        $this->logActivity("Menambahkan fakultas: {$singkatan}", 'Akreditasi', $id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Fakultas berhasil ditambahkan.',
            'data'    => $facultyModel->find($id)
        ], 201);
    }

    /**
     * PUT /api/admin/accreditation/faculties/(:num)
     */
    public function updateFaculty($id)
    {
        $facultyModel = new FacultyModel();
        $faculty = $facultyModel->find((int)$id);
        if (!$faculty) {
            return $this->respondWithError('Fakultas tidak ditemukan.', 404);
        }

        $input = $this->request->getJSON(true) ?? [];
        if (empty($input)) {
            $input = $this->request->getPost() ?? [];
        }

        $namaFakultas = trim($input['nama_fakultas'] ?? $faculty['nama_fakultas']);
        $singkatan    = trim($input['singkatan'] ?? $faculty['singkatan']);
        $website      = trim($input['website'] ?? $faculty['website']);

        if (empty($namaFakultas) || empty($singkatan)) {
            return $this->respondWithError('Nama fakultas dan singkatan wajib diisi.', 400);
        }

        // Check duplicate abbreviation
        $dup = $facultyModel->where('singkatan', $singkatan)->where('id !=', (int)$id)->first();
        if ($dup) {
            return $this->respondWithError('Fakultas dengan singkatan ini sudah ada.', 409);
        }

        $facultyModel->update((int)$id, [
            'nama_fakultas' => $namaFakultas,
            'singkatan'     => $singkatan,
            'website'       => $website ?: null,
        ]);

        $this->logActivity("Memperbarui fakultas: {$singkatan}", 'Akreditasi', (int)$id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Fakultas berhasil diperbarui.',
            'data'    => $facultyModel->find((int)$id)
        ]);
    }

    /**
     * DELETE /api/admin/accreditation/faculties/(:num)
     */
    public function deleteFaculty($id)
    {
        $facultyModel = new FacultyModel();
        $faculty = $facultyModel->find((int)$id);
        if (!$faculty) {
            return $this->respondWithError('Fakultas tidak ditemukan.', 404);
        }

        // Also clean up study program certificates
        $prodiModel = new StudyProgramModel();
        $prodis = $prodiModel->where('faculty_id', (int)$id)->findAll();
        $uploadPath = ROOTPATH . 'public/uploads/accreditation/';
        foreach ($prodis as $p) {
            foreach (['sertifikat_berlaku', 'sertifikat_periode_1', 'sertifikat_periode_2'] as $certKey) {
                if ($p[$certKey] && is_file($uploadPath . $p[$certKey])) {
                    unlink($uploadPath . $p[$certKey]);
                }
            }
        }

        $facultyModel->delete((int)$id);

        $this->logActivity("Menghapus fakultas: {$faculty['singkatan']}", 'Akreditasi', (int)$id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Fakultas dan program studi terkait berhasil dihapus.'
        ]);
    }

    /**
     * GET /api/admin/accreditation/study-programs
     */
    public function listProdis()
    {
        $prodiModel = new StudyProgramModel();

        // Search & Filters
        $search = $this->request->getGet('search');
        $facultyId = $this->request->getGet('faculty_id');
        $jenjang = $this->request->getGet('jenjang');
        $akreditasi = $this->request->getGet('akreditasi_sekarang');

        $builder = $prodiModel->select('study_programs.*, faculties.nama_fakultas, faculties.singkatan as singkatan_fakultas')
            ->join('faculties', 'faculties.id = study_programs.faculty_id');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('study_programs.nama_prodi', $search)
                ->orLike('faculties.nama_fakultas', $search)
                ->orLike('faculties.singkatan', $search)
                ->groupEnd();
        }

        if (!empty($facultyId)) {
            $builder->where('study_programs.faculty_id', (int)$facultyId);
        }

        if (!empty($jenjang)) {
            $builder->where('study_programs.jenjang', $jenjang);
        }

        if (!empty($akreditasi)) {
            $builder->where('study_programs.akreditasi_sekarang', $akreditasi);
        }

        $prodis = $builder->orderBy('study_programs.nama_prodi', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $prodis
        ]);
    }

    /**
     * POST /api/admin/accreditation/study-programs
     */
    public function createProdi()
    {
        $namaProdi          = trim($this->request->getPost('nama_prodi') ?? '');
        $facultyId          = $this->request->getPost('faculty_id');
        $jenjang            = $this->request->getPost('jenjang') ?: 'S1';
        $website            = trim($this->request->getPost('website') ?? '');
        $akreditasiSekarang = $this->request->getPost('akreditasi_sekarang') ?: 'Baik';
        $masaBerlaku        = $this->request->getPost('masa_berlaku') ?: null;

        $akreditasiP1       = $this->request->getPost('akreditasi_periode_1') ?: null;
        $masaBerlakuP1      = $this->request->getPost('masa_berlaku_periode_1') ?: null;

        $akreditasiP2       = $this->request->getPost('akreditasi_periode_2') ?: null;
        $masaBerlakuP2      = $this->request->getPost('masa_berlaku_periode_2') ?: null;

        $jalurRaw           = $this->request->getPost('jalur_masuk');
        // Handle if array or JSON string
        $jalurMasuk = null;
        if (!empty($jalurRaw)) {
            if (is_array($jalurRaw)) {
                $jalurMasuk = json_encode($jalurRaw);
            } else {
                // If it is a string representation of array or just JSON
                $decoded = json_decode($jalurRaw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $jalurMasuk = json_encode($decoded);
                } else {
                    $jalurMasuk = json_encode([$jalurRaw]);
                }
            }
        }

        if (empty($namaProdi) || empty($facultyId)) {
            return $this->respondWithError('Nama Program Studi dan Fakultas wajib diisi.', 400);
        }

        $prodiModel = new StudyProgramModel();

        $data = [
            'nama_prodi'             => $namaProdi,
            'faculty_id'             => (int)$facultyId,
            'jenjang'                => $jenjang,
            'website'                => $website ?: null,
            'akreditasi_sekarang'    => $akreditasiSekarang,
            'masa_berlaku'           => $masaBerlaku,
            'akreditasi_periode_1'   => $akreditasiP1,
            'masa_berlaku_periode_1' => $masaBerlakuP1,
            'akreditasi_periode_2'   => $akreditasiP2,
            'masa_berlaku_periode_2' => $masaBerlakuP2,
            'jalur_masuk'            => $jalurMasuk,
        ];

        $data['sertifikat_berlaku']   = $this->request->getPost('sertifikat_berlaku') ?: null;
        $data['sertifikat_periode_1'] = $this->request->getPost('sertifikat_periode_1') ?: null;
        $data['sertifikat_periode_2'] = $this->request->getPost('sertifikat_periode_2') ?: null;

        $id = $prodiModel->insert($data, true);

        $this->logActivity("Menambahkan Program Studi: {$namaProdi} ({$jenjang})", 'Akreditasi', $id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Program studi berhasil ditambahkan.',
            'data'    => $prodiModel->find($id)
        ], 201);
    }

    /**
     * POST/PUT /api/admin/accreditation/study-programs/(:num)
     */
    public function updateProdi($id)
    {
        $prodiModel = new StudyProgramModel();
        $prodi = $prodiModel->find((int)$id);
        if (!$prodi) {
            return $this->respondWithError('Program studi tidak ditemukan.', 404);
        }

        $namaProdi          = trim($this->request->getPost('nama_prodi') ?? $prodi['nama_prodi']);
        $facultyId          = $this->request->getPost('faculty_id') ?? $prodi['faculty_id'];
        $jenjang            = $this->request->getPost('jenjang') ?? $prodi['jenjang'];
        $website            = trim($this->request->getPost('website') ?? $prodi['website']);
        $akreditasiSekarang = $this->request->getPost('akreditasi_sekarang') ?? $prodi['akreditasi_sekarang'];
        $masaBerlaku        = $this->request->getPost('masa_berlaku') ?: null;

        $akreditasiP1       = $this->request->getPost('akreditasi_periode_1') ?: null;
        $masaBerlakuP1      = $this->request->getPost('masa_berlaku_periode_1') ?: null;

        $akreditasiP2       = $this->request->getPost('akreditasi_periode_2') ?: null;
        $masaBerlakuP2      = $this->request->getPost('masa_berlaku_periode_2') ?: null;

        $jalurRaw           = $this->request->getPost('jalur_masuk');
        $jalurMasuk = $prodi['jalur_masuk'];
        if ($jalurRaw !== null) {
            if (empty($jalurRaw)) {
                $jalurMasuk = null;
            } else if (is_array($jalurRaw)) {
                $jalurMasuk = json_encode($jalurRaw);
            } else {
                $decoded = json_decode($jalurRaw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $jalurMasuk = json_encode($decoded);
                } else {
                    $jalurMasuk = json_encode([$jalurRaw]);
                }
            }
        }

        if (empty($namaProdi) || empty($facultyId)) {
            return $this->respondWithError('Nama Program Studi dan Fakultas wajib diisi.', 400);
        }

        $clearCert = $this->request->getPost('clear_cert') === '1';
        $clearCertP1 = $this->request->getPost('clear_cert_periode_1') === '1';
        $clearCertP2 = $this->request->getPost('clear_cert_periode_2') === '1';

        $data = [
            'nama_prodi'             => $namaProdi,
            'faculty_id'             => (int)$facultyId,
            'jenjang'                => $jenjang,
            'website'                => $website ?: null,
            'akreditasi_sekarang'    => $akreditasiSekarang,
            'masa_berlaku'           => $masaBerlaku,
            'akreditasi_periode_1'   => $akreditasiP1,
            'masa_berlaku_periode_1' => $masaBerlakuP1,
            'akreditasi_periode_2'   => $akreditasiP2,
            'masa_berlaku_periode_2' => $masaBerlakuP2,
            'jalur_masuk'            => $jalurMasuk,
        ];

        $data['sertifikat_berlaku']   = $this->request->getPost('sertifikat_berlaku') ?: null;
        $data['sertifikat_periode_1'] = $this->request->getPost('sertifikat_periode_1') ?: null;
        $data['sertifikat_periode_2'] = $this->request->getPost('sertifikat_periode_2') ?: null;

        $prodiModel->update((int)$id, $data);

        $this->logActivity("Memperbarui Program Studi: {$namaProdi} ({$jenjang})", 'Akreditasi', (int)$id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Program studi berhasil diperbarui.',
            'data'    => $prodiModel->find((int)$id)
        ]);
    }

    /**
     * DELETE /api/admin/accreditation/study-programs/(:num)
     */
    public function deleteProdi($id)
    {
        $prodiModel = new StudyProgramModel();
        $prodi = $prodiModel->find((int)$id);
        if (!$prodi) {
            return $this->respondWithError('Program studi tidak ditemukan.', 404);
        }

        $uploadPath = ROOTPATH . 'public/uploads/accreditation/';
        foreach (['sertifikat_berlaku', 'sertifikat_periode_1', 'sertifikat_periode_2'] as $certKey) {
            if ($prodi[$certKey] && is_file($uploadPath . $prodi[$certKey])) {
                unlink($uploadPath . $prodi[$certKey]);
            }
        }

        $prodiModel->delete((int)$id);

        $this->logActivity("Menghapus Program Studi: {$prodi['nama_prodi']}", 'Akreditasi', (int)$id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Program studi berhasil dihapus.'
        ]);
    }

    /**
     * GET /api/admin/accreditation/reports
     */
    public function reports()
    {
        $facultyModel = new FacultyModel();
        $prodiModel   = new StudyProgramModel();

        $totalFaculties = $facultyModel->countAllResults();
        $totalProdis    = $prodiModel->countAllResults();
        $totalUnggul    = $prodiModel->where('akreditasi_sekarang', 'Unggul')->countAllResults();

        // 1. Accreditation ratio
        $grades = ['Unggul', 'Baik Sekali', 'Baik'];
        $accreditationRatio = [];
        foreach ($grades as $g) {
            $accreditationRatio[$g] = $prodiModel->where('akreditasi_sekarang', $g)->countAllResults();
        }

        // 2. Jenjang ratio
        $jenjangs = ['D4', 'S1', 'S2', 'S3', 'Profesi'];
        $jenjangRatio = [];
        foreach ($jenjangs as $j) {
            $jenjangRatio[$j] = $prodiModel->where('jenjang', $j)->countAllResults();
        }

        // 3. Prodis per faculty
        $faculties = $facultyModel->orderBy('nama_fakultas', 'ASC')->findAll();
        $prodiPerFaculty = [];
        foreach ($faculties as $fac) {
            $prodiPerFaculty[] = [
                'id'            => $fac['id'],
                'nama_fakultas' => $fac['nama_fakultas'],
                'singkatan'     => $fac['singkatan'],
                'count'         => $prodiModel->where('faculty_id', $fac['id'])->countAllResults()
            ];
        }

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'total_faculties'     => $totalFaculties,
                'total_prodis'        => $totalProdis,
                'total_unggul'        => $totalUnggul,
                'accreditation_ratio' => $accreditationRatio,
                'jenjang_ratio'       => $jenjangRatio,
                'prodi_per_faculty'   => $prodiPerFaculty
            ]
        ]);
    }

    /**
     * GET /api/admin/accreditation/study-programs/template
     */
    public function downloadTemplate()
    {
        $header = [
            'Nama Program Studi',
            'Fakultas (Singkatan)',
            'Jenjang',
            'Peringkat Akreditasi',
            'Masa Berlaku (DD/MM/YYYY)',
            'Link Folder Google Drive',
            'Jalur Masuk (Pisahkan dengan koma)',
            'Website Resmi'
        ];
        
        $sample = [
            [
                'Pendidikan Teknologi Informasi',
                'FTK',
                'S1',
                'Unggul',
                '26/11/2030',
                'https://drive.google.com/drive/folders/1abc123xyz',
                'SNBT, UTBK, SPAN, UM-PTKIN',
                'https://pti.ar-raniry.ac.id'
            ],
            [
                'Pascasarjana Magister Komunikasi',
                'Pasca',
                'S2',
                'Baik Sekali',
                '15/05/2028',
                'https://drive.google.com/drive/folders/2def456uvw',
                'Reguler UINAR, Portofolio UTBK',
                'https://pasca.ar-raniry.ac.id'
            ],
            [
                'Pendidikan Profesi Guru',
                'FTK',
                'Profesi',
                'Baik Sekali',
                '12/12/2029',
                'https://drive.google.com/drive/folders/3ghi789rst',
                'Reguler UINAR',
                'https://ppg.ar-raniry.ac.id'
            ]
        ];

        $filename = 'template_prodi_akreditasi.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel compatibility (UTF-8)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, $header);
        foreach ($sample as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    /**
     * POST /api/admin/accreditation/study-programs/import
     */
    public function importProdisCsv()
    {
        $db   = \Config\Database::connect();
        $file = $this->request->getFile('csv_file');

        if (!$file || !$file->isValid()) {
            return $this->respondWithError("Berkas tidak valid atau tidak ditemukan. Pastikan field 'csv_file' terisi.", 400);
        }

        if (strtolower($file->getExtension()) !== 'csv') {
            return $this->respondWithError("Format berkas wajib berupa .csv.", 400);
        }

        $filePath = $file->getTempName();
        $handle   = fopen($filePath, 'r');
        if (!$handle) {
            return $this->respondWithError("Gagal membuka berkas CSV.", 500);
        }

        // Skip header row
        fgetcsv($handle, 4096, ',');

        $stats = ['inserted' => 0, 'skipped' => 0, 'errors' => 0, 'details' => []];
        $prodiModel = new StudyProgramModel();
        $userId = $this->request->decodedToken['uid'] ?? null;

        // Cache existing faculties for efficient name/abbreviation lookups
        $facultyModel = new FacultyModel();
        $facs = $facultyModel->findAll();
        $facultyCache = [];
        foreach ($facs as $f) {
            $facultyCache[strtolower(trim($f['singkatan']))] = $f['id'];
            $facultyCache[strtolower(trim($f['nama_fakultas']))] = $f['id'];
        }

        $rowNum = 1; // row 1 was header
        $db->transBegin();
        try {
            while (($row = fgetcsv($handle, 4096, ',')) !== false) {
                $rowNum++;
                
                // Skip completely empty rows
                if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }

                if (count($row) < 3) {
                    $stats['errors']++;
                    $stats['details'][] = "Baris {$rowNum}: Kolom kurang dari 3 (Nama, Fakultas, Jenjang wajib diisi).";
                    continue;
                }

                $namaProdi       = trim($row[0] ?? '');
                $facString       = trim($row[1] ?? '');
                $jenjangRaw      = trim($row[2] ?? '');
                $akreditasi      = trim($row[3] ?? '');
                $masaBerlaku     = trim($row[4] ?? '');
                $sertifikatUrl   = trim($row[5] ?? '');
                $jalurMasukRaw   = trim($row[6] ?? '');
                $website         = trim($row[7] ?? '');

                if (empty($namaProdi)) {
                    $stats['errors']++;
                    $stats['details'][] = "Baris {$rowNum}: Nama Program Studi kosong.";
                    continue;
                }

                if (empty($facString)) {
                    $stats['errors']++;
                    $stats['details'][] = "Baris {$rowNum}: Singkatan/Nama Fakultas kosong.";
                    continue;
                }

                // Resolve Faculty ID
                $facKey = strtolower($facString);
                if (!isset($facultyCache[$facKey])) {
                    $stats['errors']++;
                    $stats['details'][] = "Baris {$rowNum}: Fakultas dengan singkatan/nama '{$facString}' tidak ditemukan.";
                    continue;
                }
                $facultyId = $facultyCache[$facKey];

                // Validate and normalize Jenjang
                $jenjangUpper = strtoupper($jenjangRaw);
                if ($jenjangUpper === 'PROFESI') {
                    $jenjang = 'Profesi';
                } elseif (in_array($jenjangUpper, ['D4', 'S1', 'S2', 'S3'])) {
                    $jenjang = $jenjangUpper;
                } else {
                    $jenjang = 'S1';
                }

                // Normalize Accreditation Rank
                $akreditasiNorm = 'Baik';
                $akreditasiLower = strtolower($akreditasi);
                if ($akreditasiLower === 'unggul') {
                    $akreditasiNorm = 'Unggul';
                } else if ($akreditasiLower === 'baik sekali' || $akreditasiLower === 'baiksekali') {
                    $akreditasiNorm = 'Baik Sekali';
                }

                // Check Duplicates: Nama Prodi + Faculty ID
                $dup = $prodiModel->where('nama_prodi', $namaProdi)
                                  ->where('faculty_id', $facultyId)
                                  ->first();
                if ($dup) {
                    $stats['skipped']++;
                    $stats['details'][] = "Baris {$rowNum}: Program studi '{$namaProdi}' di Fakultas '{$facString}' dilewati (Sudah ada).";
                    continue;
                }

                // Normalize Expiry Date (supports YYYY-MM-DD, DD-MM-YYYY, DD/MM/YYYY, YYYY/MM/DD)
                $masaBerlakuDate = null;
                if (!empty($masaBerlaku)) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $masaBerlaku)) {
                        $masaBerlakuDate = $masaBerlaku;
                    } else if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $masaBerlaku)) {
                        $parts = explode('-', $masaBerlaku);
                        $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                        $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                        $masaBerlakuDate = $parts[2] . '-' . $month . '-' . $day;
                    } else if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $masaBerlaku)) {
                        $parts = explode('/', $masaBerlaku);
                        $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                        $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                        $masaBerlakuDate = $parts[2] . '-' . $month . '-' . $day;
                    } else if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $masaBerlaku)) {
                        $masaBerlakuDate = str_replace('/', '-', $masaBerlaku);
                    }
                }

                // Normalize Jalur Masuk (splits by comma or semicolon)
                $jalurMasukArray = [];
                if (!empty($jalurMasukRaw)) {
                    $del = strpos($jalurMasukRaw, ';') !== false ? ';' : ',';
                    foreach (explode($del, $jalurMasukRaw) as $item) {
                        $trimmed = trim($item);
                        if (!empty($trimmed)) {
                            $jalurMasukArray[] = $trimmed;
                        }
                    }
                }
                $jalurMasukJson = !empty($jalurMasukArray) ? json_encode($jalurMasukArray) : null;

                // Insert into Database
                $prodiModel->insert([
                    'nama_prodi'             => $namaProdi,
                    'faculty_id'             => $facultyId,
                    'jenjang'                => $jenjang,
                    'akreditasi_sekarang'    => $akreditasiNorm,
                    'masa_berlaku'           => $masaBerlakuDate,
                    'sertifikat_berlaku'     => !empty($sertifikatUrl) ? $sertifikatUrl : null,
                    'jalur_masuk'            => $jalurMasukJson,
                    'website'                => !empty($website) ? $website : null,
                ]);

                $stats['inserted']++;
            }
            fclose($handle);

            if ($stats['inserted'] > 0) {
                $this->logActivity("Melakukan impor massal program studi via CSV. Total berhasil: " . $stats['inserted'], "Akreditasi");
            }
            $db->transCommit();

            $i = $stats['inserted'];
            $s = $stats['skipped'];
            $e = $stats['errors'];
            $stats['message'] = "{$i} program studi berhasil ditambahkan, {$s} dilewati (duplikat), {$e} baris bermasalah.";

            return $this->respond([
                'status'  => 'success',
                'message' => 'Impor CSV selesai.',
                'data'    => $stats
            ]);

        } catch (\Exception $ex) {
            fclose($handle);
            $db->transRollback();
            return $this->respondWithError("Impor gagal: " . $ex->getMessage(), 500);
        }
    }
}

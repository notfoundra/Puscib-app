<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterVolumeModel extends Model
{
    protected $table            = 'master_volume';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['keterangan', 'jarak_min', 'jarak_max', 'harga'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'        => 'integer',
        'jarak_min' => 'integer',
        'jarak_max' => 'integer',
        'harga'     => 'integer',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'keterangan' => 'required|max_length[255]',
        'jarak_min'  => 'required|is_natural',
        'jarak_max'  => 'required|is_natural_no_zero|greater_than[{jarak_min}]',
        'harga'      => 'required|is_natural',
    ];
    protected $validationMessages = [
        'jarak_max' => ['greater_than' => 'Jarak maksimum harus lebih besar dari jarak minimum.'],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /** Semua tier, urut dari jarak terdekat. Dipakai untuk kolom dinamis di laporan. */
    public function tiers(): array
    {
        return $this->orderBy('jarak_min', 'ASC')->orderBy('jarak_max', 'ASC')->findAll();
    }

    /** True kalau rentang baru tumpang tindih dengan tier lain. */
    public function bentrok(int $min, int $max, ?int $abaikanId = null): bool
    {
        $q = $this->where('jarak_min <', $max)->where('jarak_max >', $min);

        if ($abaikanId !== null) {
            $q->where('id !=', $abaikanId);
        }

        return $q->countAllResults() > 0;
    }
}

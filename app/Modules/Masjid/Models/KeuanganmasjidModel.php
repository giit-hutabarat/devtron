<?php

namespace App\Modules\Masjid\Models;

use CodeIgniter\Model;

class KeuanganmasjidModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'keuanganmasjids';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function nominal_pemasukan(){
        $bulan = date("n");
        $this->select('sum(pemasukan) as pemasukan');
        $this->where('MONTH(tanggal) =', $bulan);
		return $this->get()->getRow()->pemasukan;
	}
	
	public function nominal_pengeluaran(){
        $bulan = date("n");
        $this->select('sum(pengeluaran) as pengeluaran');
        $this->where('MONTH(tanggal) =', $bulan);
		return $this->get()->getRow()->pengeluaran;
	}	

    public function get_transaksi(){
        $bulan = date("n");
        $this->select("{$this->table}.*");
        $this->where('MONTH(tanggal) =', $bulan);
        $this->orderBy("{$this->table}.tanggal", "DESC");
        $query = $this->findAll();
		return $query;		
	}
}

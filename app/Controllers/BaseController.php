<?php

namespace App\Controllers;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
11-2021
*/

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController ini digunakan untuk semua Controller yang merender VIEW (Web App/Admin Page).
 *
 * @package CodeIgniter
 */
class BaseController extends Controller
{
	/**
	 * Instance dari objek Request utama.
	 *
	 * @var CLIRequest|IncomingRequest
	 */
	protected $request;

	/**
	 * Array berisi helper yang dimuat secara otomatis.
	 *
	 * @var array
	 */
	protected $helpers = [];

	/**
	 * Properti Session
	 * Dideklarasikan di sini agar tersedia di semua turunan Controller.
	 * * @var \CodeIgniter\Session\Session
	 */
	protected $session; 

	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		// JANGAN UBAH BARIS INI
		parent::initController($request, $response, $logger);

		//--------------------------------------------------------------------
		// Preload models, libraries, dll, di sini.
		//--------------------------------------------------------------------
		$config = config("App");
		
		// Inisialisasi Session dan Language
		$this->session = \Config\Services::session();
		$language = \Config\Services::language();
		$language->setLocale($this->session->lang ?? $config->defaultLocale);
	}
}
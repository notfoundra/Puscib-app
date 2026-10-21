<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /** Tersedia di semua controller dan view (format_rupiah, terbilang, dst). */
    protected $helpers = ['form', 'url', 'laporan'];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /** Hentikan request kalau user tidak punya permission. */
    protected function izin(string $permission): void
    {
        if (! auth()->loggedIn() || ! auth()->user()->can($permission)) {
            throw PageNotFoundException::forPageNotFound('Anda tidak punya akses ke halaman ini.');
        }
    }

    protected function kembali(string $tipe, string|array $pesan, bool $denganInput = false)
    {
        $r = redirect()->back()->with($tipe, $pesan);

        return $denganInput ? $r->withInput() : $r;
    }
}

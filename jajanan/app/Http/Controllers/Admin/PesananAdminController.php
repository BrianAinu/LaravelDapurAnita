<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Resi;
use Illuminate\Http\Request;

class PesananAdminController extends Controller
{
    // ===============================
    //  API UNTUK MOBILE ADMIN
    // ===============================

    // GET /api/pesananAdminApi
    public function indexApi()
    {
        $pesanan = Pesanan::leftJoin('resi','resi.id_pesanan','=','pesanan.id_pesanan')
            ->join('produk', 'produk.id_produk', '=', 'pesanan.id_produk')
            ->join('users', 'users.id', '=', 'pesanan.id_user')
            ->orderBy('pesanan.created_at', 'desc')
            ->select('pesanan.*', 'produk.nama_produk','produk.harga_produk','produk.foto_produk','users.name as name','resi.no_resi')
            ->get();

        return response()->json(['data' => $pesanan]);
    }

    // PUT /api/pesananAdminKonfirmasiApi/{id}
    public function konfirmasiApi($id)
    {
        return $this->updateStatus($id, 1);
    }

    // GET /api/pengirimanAdminApi (list pesanan status 3)
    public function pengirimanApi()
    {
        $pesanan = Pesanan::leftJoin('resi','resi.id_pesanan','=','pesanan.id_pesanan')
            ->join('produk','produk.id_produk','=','pesanan.id_produk')
            ->join('users','users.id','=','pesanan.id_user')
            ->select('pesanan.*','produk.nama_produk','produk.harga_produk','produk.foto_produk','resi.no_resi','users.name as name')
            ->where('pesanan.status',3)
            ->orderBy('pesanan.updated_at','desc')
            ->get();
        return response()->json(['data'=>$pesanan]);
    }

    // POST /api/pesananAdminKirimApi -> body: id_pesanan,no_resi
    public function pesananKirimApi(Request $request)
    {
        $request->validate([
            'id_pesanan'=> 'required|exists:pesanan,id_pesanan',
            'no_resi'   => 'required|string'
        ]);

        Resi::updateOrCreate(['id_pesanan'=>$request->id_pesanan],[
            'no_resi'=>$request->no_resi
        ]);

        Pesanan::where('id_pesanan',$request->id_pesanan)->update(['status'=>3]);

        return response()->json(['message'=>'Pesanan dikirim','id_pesanan'=>$request->id_pesanan]);
    }

    // GET /api/resiApi/{id_pesanan}
    public function resiByPesananApi($id)
    {
        $detail = Pesanan::leftJoin('resi','resi.id_pesanan','=','pesanan.id_pesanan')
            ->join('produk','produk.id_produk','=','pesanan.id_produk')
            ->join('users','users.id','=','pesanan.id_user')
            ->select('pesanan.*',
                'produk.nama_produk','produk.harga_produk','produk.foto_produk',
                'users.name as user_name','users.email as user_email',
                'resi.no_resi','resi.id_resi')
            ->find($id);
        if(!$detail){
            return response()->json(['message'=>'Pesanan tidak ditemukan'],404);
        }
        return response()->json(['data'=>$detail]);
    }

    // PUT /api/pesananAdminStatusApi/{id}/{status}
    public function updateStatusApi($id, $status)
    {
        return $this->updateStatus($id, $status);
    }

    private function updateStatus($id, $status)
    {
        $pesanan = Pesanan::find($id);
        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }
        $pesanan->status = $status;
        $pesanan->save();
        return response()->json(['message' => 'Status diperbarui', 'status' => $status]);
    }

    public function lihat_pesanan()
    {
        $pesanan = Pesanan::join('produk','produk.id_produk','=','pesanan.id_produk')
        ->join('alamat_user','alamat_user.id_user','=','pesanan.id_user')
        ->select('pesanan.*','alamat_user.alamat_lengkap','alamat_user.nama_penerima','alamat_user.no_telp','alamat_user.nama_prov','alamat_user.nama_kota','alamat_user.no_telp','produk.nama_produk','produk.harga_produk','produk.foto_produk','produk.berat')
        ->where('pesanan.status', 1)
        ->get();
        return view('admin.pesanan.pesanan_list', compact(['pesanan']));
    }

    public function terima_pesanan($id)
    {
        $pesanan = Pesanan::find($id);
        $id_produk = $pesanan->id_produk;
        Produk::where('id_produk', $id_produk)
        ->decrement('stok', $pesanan->quantity);
        Pesanan::find($id)->update([
            'status'=>2
        ]);

        return to_route('admin.pesanan_prosses');
    }

    public function tolak_pesanan($id)
    {
        Pesanan::find($id)->update([
            'status'=>0
        ]);

        return back();
    }

    public function pesanan_onprosses()
    {
        $pesanan = Pesanan::join('produk','produk.id_produk','=','pesanan.id_produk')
        ->join('alamat_user','alamat_user.id_user','=','pesanan.id_user')
        ->select('pesanan.*','alamat_user.alamat_lengkap','alamat_user.nama_penerima','alamat_user.no_telp','alamat_user.nama_prov','alamat_user.nama_kota','alamat_user.no_telp','produk.nama_produk','produk.harga_produk','produk.foto_produk','produk.berat')
        ->where('pesanan.status', 2)
        ->get();

        return view('admin.pesanan.pesanan_onprosses', compact(['pesanan']));
    }

    public function invoice($id)
    {
        $pesanan = Pesanan::join('produk','produk.id_produk','=','pesanan.id_produk')
        ->join('alamat_user','alamat_user.id_user','=','pesanan.id_user')
        ->select('pesanan.*','alamat_user.alamat_lengkap','alamat_user.nama_penerima','alamat_user.no_telp','alamat_user.nama_prov','alamat_user.nama_kota','alamat_user.no_telp','produk.nama_produk','produk.harga_produk','produk.foto_produk','produk.berat')
        ->find($id);
        return view('admin.invoice.invoice', compact(['pesanan']));
    }

    public function pesanan_kirim(Request $request)
    {
        Resi::create([
            'id_pesanan'=>$request->id_pesanan,
            'no_resi'=>$request->resi
        ]);

        $id = $request->id_pesanan;

        Pesanan::find($id)->update([
            'status'=>'3'
        ]);

        return to_route('admin.pesanan_deliver');
    }

    public function pesanan_deliver()
    {
        $pesanan = Pesanan::join('produk','produk.id_produk','=','pesanan.id_produk')
        ->join('alamat_user','alamat_user.id_user','=','pesanan.id_user')
        ->select('pesanan.*','alamat_user.alamat_lengkap','alamat_user.nama_penerima','alamat_user.no_telp','alamat_user.nama_prov','alamat_user.nama_kota','alamat_user.no_telp','produk.nama_produk','produk.harga_produk','produk.foto_produk','produk.berat')
        ->where('pesanan.status', 3)
        ->get();

        return view('admin.pesanan.pesanan_deliver', compact(['pesanan']));
    }

    public function pesanan_dp_tagihan($id)
    {
        Pesanan::find($id)->update([
            'dp_status'=>'tagihan',
        ]);
        return back();
    }

    public function tolak_sisa($id)
    {
        Pesanan::find($id)->update([
            'dp_status'=>'sisa tolak',
        ]);
        return back();
    }
}

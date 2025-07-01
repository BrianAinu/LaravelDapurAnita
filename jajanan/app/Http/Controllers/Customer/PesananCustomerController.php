<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\Keranjang;
use App\Models\Komentar;
use App\Models\Pesanan;
use App\Models\Rekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesananCustomerController extends Controller
{
    public function get_ongkir($id_kota, $berat)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "origin=399&destination=" . $id_kota . "&weight=" . $berat . "&courier=jne",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: f201c33f7b1021a48e2a76125bfa5e15"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $response = json_decode($response, true);
            $provinsi = $response['rajaongkir']['results'];
            return $provinsi;
        }
    }


    public function index()
    {
        $pesanan_onpaid = Pesanan::join('produk','produk.id_produk','=','pesanan.id_produk')
        ->join('alamat_user','alamat_user.id_user','=','pesanan.id_user')
        ->select('pesanan.*', 'produk.nama_produk','produk.foto_produk','alamat_user.nama_kota','alamat_user.nama_prov')
        ->where('pesanan.id_user', Auth::user()->id)
        ->where(function($query){
            $query->where('pesanan.status', 0)
            ->orWhere('pesanan.status', 1)
            ->orWhere('pesanan.status', 2);
        })
        ->orderBy('pesanan.updated_at', 'desc')
        ->get();

        return view('customer.pesanan.pesanan', compact(['pesanan_onpaid']));
    }

    public function store_pesanan(Request $request)
    {
        $request->validate([
            'bukti_bayar'=>'required',
            'metode'=>'required'
        ]);

        if ($request->hasFile('bukti_bayar')) {
            $bukti_bayar= $request->file('bukti_bayar')->getClientOriginalName();
            $request->bukti_bayar->move(public_path('bukti_bayar'), $bukti_bayar);
        }
        $id = $request->id_keranjang;

        if ($request->metode=="dp") {
            Pesanan::create([
                'id_produk'=>$request->id_produk,
                'id_user'=>$request->id_user,
                'quantity'=>$request->quantity,
                'harga_total_bayar'=>$request->harga_produk,
                'ongkir'=>$request->ongkir,
                'total_ongkir'=>$request->total_bayar,
                'bukti_bayar_dp'=>$bukti_bayar,
                'total_dp'=>$request->dp,
                'dp_status'=>'dp',
                'status'=>'1',
                'tipe_pembayaran'=>$request->metode,
            ]);
        } else {
            Pesanan::create([
                'id_produk'=>$request->id_produk,
                'id_user'=>$request->id_user,
                'quantity'=>$request->quantity,
                'harga_total_bayar'=>$request->harga_produk,
                'ongkir'=>$request->ongkir,
                'total_ongkir'=>$request->total_bayar,
                'bukti_bayar'=>$bukti_bayar,
                'status'=>'1',
                'tipe_pembayaran'=>$request->metode,
            ]);
        }

        Keranjang::find($id)->delete();

        return to_route('customer.pesanan');
    }

    public function upload_ulang($id)
    {
        $cek_alamat = Alamat::where('id_user', Auth::user()->id)->first();
        if ($cek_alamat == NULL) {
            return to_route('alamat.create');
        }


        $pengiriman = Alamat::where('id_user', Auth::user()->id)->first();
        $keranjang = Pesanan::join('produk','produk.id_produk','=','pesanan.id_produk')
        ->select('pesanan.*','produk.nama_produk','produk.harga_produk','produk.foto_produk','produk.berat')
        ->find($id);
        $rekening = Rekening::orderBy('jenis_rekening', 'asc')->get();

        $berat = $keranjang->berat * $keranjang->quantity;
        $id_kota =$pengiriman->id_kota;

        $ongkir = $this->get_ongkir($id_kota, $berat);

        return view('customer.checkout.checkout_ulang', compact(['keranjang','ongkir','berat','pengiriman','rekening']));
    }

    public function upload_store(Request $request)
    {
        $request->validate([
            'bukti_bayar'=>'required',
            'metode'=>'required'
        ]);

        if ($request->hasFile('bukti_bayar')) {
            $bukti_bayar= $request->file('bukti_bayar')->getClientOriginalName();
            $request->bukti_bayar->move(public_path('bukti_bayar'), $bukti_bayar);
        }

        $id = $request->id_pesanan;

        if ($request->metode=="dp") {
            Pesanan::find($id)->update([
                'total_ongkir'=>$request->total_bayar,
                'bukti_bayar_dp'=>$bukti_bayar,
                'dp_status'=>'dp',
                'status'=>1,
                'tipe_pembayaran'=>$request->metode,
            ]);
        } else {
            Pesanan::find($id)->update([
                'total_ongkir'=>$request->total_bayar,
                'bukti_bayar'=>$bukti_bayar,
                'status'=>1,
                'tipe_pembayaran'=>$request->metode,
            ]);
        }

        return to_route('customer.pesanan');
    }

    public function invoice($id)
    {
        $pesanan = Pesanan::join('produk','produk.id_produk','=','pesanan.id_produk')
        ->join('alamat_user','alamat_user.id_user','=','pesanan.id_user')
        ->select('pesanan.*','alamat_user.alamat_lengkap','alamat_user.nama_penerima','alamat_user.no_telp','alamat_user.nama_prov','alamat_user.nama_kota','alamat_user.no_telp','produk.nama_produk','produk.harga_produk','produk.foto_produk','produk.berat')
        ->find($id);
        return view('customer.invoice.invoice', compact(['pesanan']));
    }

    public function pesanan_deliver()
    {
        $pesanan = Pesanan::join('produk','produk.id_produk','=','pesanan.id_produk')
        ->join('alamat_user','alamat_user.id_user','=','pesanan.id_user')
        ->join('resi','resi.id_pesanan','=','pesanan.id_pesanan')
        ->select('pesanan.*','resi.no_resi','produk.nama_produk','produk.foto_produk','alamat_user.nama_kota','alamat_user.nama_prov')
        ->where('pesanan.id_user', Auth::user()->id)
        ->where('pesanan.status', 3)
        ->orderBy('pesanan.updated_at', 'desc')
        ->get();

        return view('customer.pesanan.pesanan_deliver', compact(['pesanan']));
    }

    public function pesanan_diterima(Request $request)
    {
        $id = $request->id_pesanan;

        Pesanan::find($id)->update([
            'status'=>'4'
        ]);

        Komentar::create([
            'id_user'=>Auth::user()->id,
            'id_produk'=>$request->id_produk,
            'komentar_produk'=>$request->komentar
        ]);

        return back();
    }

    public function upload_sisa_tagihan(Request $request)
    {
        if ($request->hasFile('bukti_bayar')) {
            $bukti_bayar= $request->file('bukti_bayar')->getClientOriginalName();
            $request->bukti_bayar->move(public_path('bukti_bayar'), $bukti_bayar);
        }

        $id = $request->id;

        Pesanan::find($id)->update([
            'bukti_bayar_dp_lunas'=>$bukti_bayar,
            'dp_status'=>'upload_lunas'
        ]);

        return back();
    }

    // ===============================
    //  API UNTUK MOBILE
    // ===============================

    // GET /api/pesanan/indexApi
    public function indexApi(Request $request)
    {
        $user = $request->user();
        $idUser = $user?->id ?? $request->query('id_user');
        if (!$idUser) {
            return response()->json(['message' => 'ID user tidak ditemukan'], 400);
        }
        $pesanan = Pesanan::join('produk','produk.id_produk','=','pesanan.id_produk')
            ->join('users','users.id','=','pesanan.id_user')
            ->where('pesanan.id_user', $idUser)
            ->orderBy('pesanan.created_at', 'desc')
            ->select('pesanan.*','produk.nama_produk','produk.foto_produk','users.name as name')
            ->get();

        return response()->json(['data' => $pesanan]);
    }

    // POST /api/pesanan/storeApi
    public function storeApi(Request $request)
    {
        $request->validate([
            'id_keranjang'    => 'required|exists:keranjang,id_keranjang',
            'tipe_pembayaran' => 'required|in:lunas,dp',
            'bukti_bayar'    => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $keranjang = Keranjang::with('produk')->find($request->id_keranjang);
        $totalBayar = $keranjang->quantity * $keranjang->produk->harga_produk;

        // handle optional image
        $buktiBayarName = null;
        if ($request->hasFile('bukti_bayar')) {
            $buktiBayarName = time().'_'.$request->file('bukti_bayar')->getClientOriginalName();
            $request->file('bukti_bayar')->move(public_path('bukti_bayar'), $buktiBayarName);
        }

        $pesanan = Pesanan::create([
            'id_produk'         => $keranjang->id_produk,
            'id_user'           => $keranjang->id_user,
            'quantity'          => $keranjang->quantity,
            'harga_total_bayar' => $totalBayar,
            'ongkir'           => 0,
            'total_ongkir'     => 0,
            'status'            => 0,
            'dp_status'        => 'lunas',
            'tipe_pembayaran'   => $request->tipe_pembayaran,
            'bukti_bayar'      => $buktiBayarName,
        ]);

        $keranjang->delete();

        return response()->json($pesanan, 201);
    }

    // POST /api/pesanan/cancelApi
    public function cancelApi(Request $request)
    {
        $request->validate([
            'id_pesanan' => 'required|exists:pesanan,id_pesanan',
        ]);

        $pesanan = Pesanan::find($request->id_pesanan);
        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        // Kembalikan ke keranjang
        Keranjang::create([
            'id_user'   => $pesanan->id_user,
            'id_produk' => $pesanan->id_produk,
            'quantity'  => $pesanan->quantity,
        ]);

        $pesanan->delete();

        return response()->json(['message' => 'Pesanan dibatalkan dan dikembalikan ke keranjang']);
    }

    public function history()
    {
        $pesanan = Pesanan::join('produk','produk.id_produk','=','pesanan.id_produk')
        ->join('alamat_user','alamat_user.id_user','=','pesanan.id_user')
        ->join('resi','resi.id_pesanan','=','pesanan.id_pesanan')
        ->select('pesanan.*','resi.no_resi','produk.nama_produk','produk.foto_produk','alamat_user.nama_kota','alamat_user.nama_prov')
        ->where('pesanan.id_user', Auth::user()->id)
        ->where(function($query){
            $query->where('pesanan.status', 4);
        })
        ->orderBy('pesanan.updated_at', 'desc')
        ->get();

        return view('customer.pesanan.pesanan_history', compact(['pesanan']));
    }
}

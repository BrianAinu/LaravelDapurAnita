<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeranjangCustomerController extends Controller
{
    public function index()
    {
        $keranjang = Keranjang::join('produk','produk.id_produk','=','keranjang.id_produk')
        ->join('kategori','kategori.id_kategori','=','produk.id_kategori')
        ->select('keranjang.*','produk.nama_produk','produk.harga_produk','produk.foto_produk','kategori.nama_kategori')
        ->where('keranjang.id_user', Auth::user()->id)
        ->get();

        return view('customer.keranjang.keranjang', compact(['keranjang']));
    }

    public function store(Request $request)
    {
        $id = $request->id_produk;
        $produk = Produk::find($id);

        if ($request->quantity > $produk->stok) {
            return back()->with('gagal', 'Maaf Jumlah Pembelian Anda Melebihi Stok yang tersedia');
        }

        Keranjang::create([
            'id_user'=>Auth::user()->id,
            'id_produk'=>$request->id_produk,
            'quantity'=>$request->quantity
        ]);

        return to_route('customer.keranjang');
    }

    public function update(Request $request, $id)
    {
        $id_produk = $request->id_produk;
        $produk = Produk::find($id_produk);

        if ($request->quantity > $produk->stok) {
            return back()->with('gagal', 'Maaf Jumlah Pembelian Anda Melebihi Stok yang tersedia');
        }

        Keranjang::find($id)->update([
            'quantity'=>$request->quantity
        ]);

        return view('customer.keranjang.keranjang');
    }

    public function delete($id)
    {
        Keranjang::find($id)->delete();
        return back()->with('gagal', 'Berhasil Menghapus Produk Dari Keranjang');
    }

    /* ===================== API for Flutter ===================== */
    // GET /api/keranjang/indexApi
    public function indexApi(Request $request)
    {
        $userId = Auth::id() ?? $request->query('id_user');
        $data = Keranjang::join('produk','produk.id_produk','=','keranjang.id_produk')
            ->select(
                'keranjang.id_keranjang as id',
                'keranjang.id_user',
                'keranjang.id_produk',
                'keranjang.quantity as qty',
                'produk.nama_produk',
                'produk.harga_produk',
                'produk.foto_produk'
            )
            ->where('keranjang.id_user', $userId)
            ->get();
        return response()->json($data);
    }

    // POST /api/keranjang/storeApi
    public function storeApi(Request $request)
    {
        $request->validate([
            'id_produk' => 'required|exists:produk,id_produk',
            'quantity'  => 'required|integer|min:1',
        ]);

        $userId = Auth::id() ?? $request->input('id_user');
        $produk  = Produk::find($request->id_produk);
        $cart    = Keranjang::where('id_user', $userId)
                    ->where('id_produk', $produk->id_produk)
                    ->first();

        $newQty = $request->quantity;
        if ($cart) {
            $newQty += $cart->quantity;
        }

        if ($newQty > $produk->stok) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah melebihi stok tersedia',
            ], 400);
        }

        if ($cart) {
            $cart->update(['quantity' => $newQty]);
        } else {
            Keranjang::create([
                'id_user'   => $userId,
                'id_produk' => $produk->id_produk,
                'quantity'  => $newQty,
            ]);
        }

        return response()->json(['success' => true], 201);
    }

    // PUT /api/keranjang/updateApi/{id}
    public function updateApi(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer', // boleh 0 atau lebih
        ]);

        $cart = Keranjang::find($id);
        if (!$cart) {
            return response()->json(['success' => false, 'message' => 'Keranjang tidak ditemukan'], 404);
        }
        $produk = Produk::find($cart->id_produk);
        $newQty = (int) $request->quantity;

        if ($newQty < 1) {
            // jika kurang dari 1, hapus item
            $cart->delete();
            return response()->json(['success' => true, 'deleted' => true]);
        }
        if ($newQty > $produk->stok) {
            return response()->json(['success' => false, 'message' => 'Jumlah melebihi stok tersedia'], 400);
        }

        $cart->update(['quantity' => $newQty]);
        return response()->json(['success' => true]);
    }

    // DELETE /api/keranjang/deleteApi/{id}
    public function deleteApi($id)
    {
        Keranjang::where('id_keranjang', $id)->delete();
        return response()->json(['success' => true]);
    }

}

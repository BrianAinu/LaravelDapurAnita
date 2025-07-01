<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;

class ProdukAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $produk = Produk::join('kategori','kategori.id_kategori','=','produk.id_kategori')
        ->orderBy('nama_produk', 'asc')
        ->get();
        return view('admin.produk.produk', compact(['produk']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();
        return view('admin.produk.produk_create', compact(['kategori']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {  
        $request->validate([
            'nama_produk'=>'required|unique:produk,nama_produk',
            'stok_produk'=>'required',
            'berat_produk'=>'required',
            'harga_produk'=>'required',
            'deskripsi_produk'=>'required',
            'img1'=>'required',
        ]);

        $harga = preg_replace("/[^0-9]/", "", $request->harga_produk);

        if ($request->hasFile('img1')) {
            $foto_produk= $request->file('img1')->getClientOriginalName();
            $request->img1->move(public_path('produk'), $foto_produk);
        }

        Produk::create([
            'nama_produk'=>$request->nama_produk,
            'id_kategori'=>$request->kategori_produk,
            'berat'=>$request->berat_produk,
            'stok'=>$request->stok_produk,
            'harga_produk'=>$harga,
            'deskripsi_produk'=>$request->deskripsi_produk,
            'foto_produk'=>$foto_produk,
        ]);

        return to_route('produk.index')->with('success','Berhasil Menambahkan Produk Baru');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $produk = Produk::find($id);
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();
        return view('admin.produk.produk_edit', compact(['kategori','produk']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_produk'=>'required',
            'stok_produk'=>'required',
            'berat_produk'=>'required',
            'harga_produk'=>'required',
            'deskripsi_produk'=>'required',
        ]);

        $harga = preg_replace("/[^0-9]/", "", $request->harga_produk);

        if ($request->hasFile('img1')) {
            $foto_produk= $request->file('img1')->getClientOriginalName();
            $request->img1->move(public_path('produk'), $foto_produk);

        }else{
            $foto_produk = $request->foto_lama;
        }

        Produk::find($id)->update([
            'nama_produk'=>$request->nama_produk,
            'id_kategori'=>$request->kategori_produk,
            'berat'=>$request->berat_produk,
            'stok'=>$request->stok_produk,
            'harga_produk'=>$harga,
            'deskripsi_produk'=>$request->deskripsi_produk,
            'foto_produk'=>$foto_produk,
        ]);

        return to_route('produk.index')->with('success','Berhasil Memperbaharui Produk');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        Produk::find($id)->delete();
        return to_route('produk.index')->with('delete','Berhasil Menghapus Produk');
    }

    public function kategoriApi()
    {
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();
        return response()->json($kategori);
    }

    public function storeApi(Request $request)
    {
       //return response()->json(($request->img1));
        $request->validate([
            'nama_produk'=>'required|unique:produk,nama_produk',
            'stok_produk'=>'required',
            'berat_produk'=>'required',
            'harga_produk'=>'required',
            'deskripsi_produk'=>'required',
            'img1'=>'required',
        ]);

        $harga = preg_replace("/[^0-9]/", "", $request->harga_produk);

        if ($request->hasFile('img1')) {
            $foto_produk= $request->file('img1')->getClientOriginalName();
            $request->img1->move(public_path('produk'), $foto_produk);

        }
        /*if(!empty($request->img1)){
            $file = base64_decode($request->img1);
            //$folderName = 'produk';
            $safeName = str_random(10).'.'.'png';
            $destinationPath = public_path('produk');
            file_put_contents(public_path('produk').$safeName, $file);

            //file_put_contents(public_path().'produk'.$imageName, base64_decode($image));
            //Storage::disk('produk')->put($imageName, base64_decode($image));
        }*/

        $produk=Produk::create([
            'nama_produk'=>$request->nama_produk,
            'id_kategori'=>$request->kategori_produk,
            'berat'=>$request->berat_produk,
            'stok'=>$request->stok_produk,
            'harga_produk'=>$harga,
            'deskripsi_produk'=>$request->deskripsi_produk,
            'foto_produk'=>$foto_produk,
        ]);

        return response()->json($produk);
    }
    public function editApi($id)
    {
        
        $produk = Produk::find($id);
        //dd($produk);
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();
        return response()->json(['user'  => $produk]);
    }

    public function updateApi(Request $request, $id)
    {
        //return response()->json(($request->img1));
        $request->validate([
            'nama_produk'=>'required',
            'stok_produk'=>'required',
            'berat_produk'=>'required',
            'harga_produk'=>'required',
            'deskripsi_produk'=>'required',
        ]);

        $harga = preg_replace("/[^0-9]/", "", $request->harga_produk);

        if ($request->hasFile('img1')) {
            $foto_produk= $request->file('img1')->getClientOriginalName();
            $request->img1->move(public_path('produk'), $foto_produk);

        } else if($request->img1!=""){
            $foto_produk = $request->img1;
        } else {
            $foto_produk = $request->foto_lama;
        }

        $produk=Produk::find($id)->update([
            'nama_produk'=>$request->nama_produk,
            'id_kategori'=>$request->kategori_produk,
            'berat'=>$request->berat_produk,
            'stok'=>$request->stok_produk,
            'harga_produk'=>$harga,
            'deskripsi_produk'=>$request->deskripsi_produk,
            'foto_produk'=>$foto_produk,
        ]);

        return response()->json($produk);
    }
    public function destroyApi($id)
    {
        //
        Produk::find($id)->delete();
        return response()->json("Berhasil di Hapus");
    }
    public function userApi()
    {
        $kategori = User::orderBy('name', 'asc')->get();
        return response()->json($kategori);
    }
}

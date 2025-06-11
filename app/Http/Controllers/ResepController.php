<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResepRequest;
use App\Http\Requests\UpdateResepRequest;
use App\Models\Resep;
use App\Models\User;
use Illuminate\Http\Request;

class ResepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('sanctum')->user();
        $resep = $user
            ? Resep::where('id_user', null)->orWhere('id_user', $user->id_user)->get()
            : Resep::where("id_user", null)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data resep berhasil diperoleh',
            'data' => $resep->map(function ($resep) use ($user) {
                return [
                    'id_resep' => $resep->id_resep,
                    'judul' => $resep->judul,
                    'kategori' => $resep->kategori,
                    'deskripsi' => $resep->deskripsi,
                    'mine' => $user && $resep->id_user === $user->id_user ? "1" : "0"
                ];
            })
        ]);
    }

    public function getImage($id_resep)
    {
        $resep = Resep::find($id_resep);

        if ($resep) {
            $path = public_path($resep->imageUrl);
            return response()->file($path);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreResepRequest $request)
    {
        $request->validate([
            "judul" => ["required", "string", "max:255"],
            "kategori" => ["required", "string"],
            "deskripsi" => ["required", "string"],
            "image" => ["required", "image"],
        ]);

        $image = $request->image;
        $filename = 'image_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('upload'), $filename);

        Resep::create([
            "judul" => $request->judul,
            "kategori" => $request->kategori,
            "deskripsi" => $request->deskripsi,
            "imageUrl" => "upload/$filename",
            "id_user" => $request->user()->id_user
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Resep baru berhasil ditambahkan'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Resep $resep)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resep $resep)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResepRequest $request, $id_resep)
    {
        $request->validate([
            "judul" => ["required", "string", "max:255"],
            "kategori" => ["required", "string"],
            "deskripsi" => ["required", "string"],
            "image" => ["image"],
        ]);

        $resep = Resep::find($id_resep);

        if (!$resep) {
            return response()->json([
                'success' => false,
                'message' => 'Resep tidak ditemukan di sistem',
            ]);
        }

        if ($resep->id_user != $request->user()->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengubah resep ini',
            ]);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'image_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('upload'), $filename);
            $resep->imageUrl = "upload/$filename";
        }

        $resep->judul = $request->judul;
        $resep->kategori = $request->kategori;
        $resep->deskripsi = $request->deskripsi;
        $resep->save();

        return response()->json([
            'success' => true,
            'message' => 'Informasi resep berhasil diperbarui',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id_resep)
    {
        $resep = Resep::find($id_resep);

        if (!$resep) {
            return response()->json([
                'success' => false,
                'message' => 'Data resep tidak dapat ditemukan',
            ]);
        }

        if ($resep->id_user != $request->user()->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda bukan pemilik resep ini',
            ]);
        }

        $resep->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resep telah dihapus dengan sukses',
        ]);
    }
}

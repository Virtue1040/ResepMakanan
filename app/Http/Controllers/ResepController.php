<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResepRequest;
use App\Http\Requests\UpdateResepRequest;
use App\Models\Resep;
use Illuminate\Http\Request;

class ResepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('sanctum')->user();

        $reseps = $user
            ? Resep::whereNull('id_user')->orWhere('id_user', $user->id_user)->get()
            : Resep::whereNull('id_user')->get();

        return $reseps->map(function ($resep) use ($user) {
            return [
                'id_resep' => $resep->id_resep,
                'judul' => $resep->judul,
                'kategori' => $resep->kategori,
                'deskripsi' => $resep->deskripsi,
                'mine' => $user && $resep->id_user === $user->id_user ? "1" : "0"
            ];
        });
    }

    /**
     * Get image file from resep.
     */
    public function getImage($id_resep)
    {
        $resep = Resep::find($id_resep);

        if ($resep && file_exists(public_path($resep->imageUrl))) {
            return response()->file(public_path($resep->imageUrl));
        }

        return response()->json([
            'success' => false,
            'message' => 'Gambar tidak ditemukan',
        ]);
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

        $resep = Resep::create([
            "judul" => $request->judul,
            "kategori" => $request->kategori,
            "deskripsi" => $request->deskripsi,
            "imageUrl" => "upload/$filename",
            "id_user" => $request->user()->id_user
        ]);

        return $resep;
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

        return $resep;
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

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResepRequest;
use App\Http\Requests\UpdateResepRequest;
use App\Models\Resep;
use Illuminate\Http\Request;

class ResepController extends Controller
{
    public function index()
    {
        $user = auth('sanctum')->user();

        $reseps = $user
            ? Resep::whereNull('id_user')->orWhere('id_user', $user->id_user)->get()
            : Resep::whereNull('id_user')->get();

        return response()->json(
            $reseps->map(function ($resep) use ($user) {
                return [
                    'id_resep' => $resep->id_resep,
                    'judul' => $resep->judul,
                    'kategori' => $resep->kategori,
                    'deskripsi' => $resep->deskripsi,
                    'mine' => $user && $resep->id_user === $user->id_user ? "1" : "0"
                ];
            })
        );
    }

    public function getImage($id_resep)
    {
        $resep = Resep::find($id_resep);

        if ($resep && file_exists(public_path($resep->imageUrl))) {
            return response()->file(public_path($resep->imageUrl));
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gambar tidak ditemukan',
        ], 404);
    }

    public function store(StoreResepRequest $request)
    {
        $request->validate([
            "judul" => ["required", "string", "max:255"],
            "kategori" => ["required", "string"],
            "deskripsi" => ["required", "string"],
            "image" => ["required", "image"],
        ]);

        $image = $request->file('image');
        $filename = 'image_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('upload'), $filename);

        $resep = Resep::create([
            "judul" => $request->judul,
            "kategori" => $request->kategori,
            "deskripsi" => $request->deskripsi,
            "imageUrl" => "upload/$filename",
            "id_user" => $request->user()->id_user
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Resep berhasil ditambahkan',
        ]);
    }

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
                'status' => 'error',
                'message' => 'Resep tidak ditemukan di sistem',
            ]);
        }

        if ($resep->id_user != $request->user()->id_user) {
            return response()->json([
                'status' => 'error',
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
            'status' => 'success',
            'message' => 'Resep berhasil diperbarui',
        ]);
    }

    public function destroy(Request $request, $id_resep)
    {
        $resep = Resep::find($id_resep);

        if (!$resep) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data resep tidak dapat ditemukan',
            ]);
        }

        if ($resep->id_user != $request->user()->id_user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan pemilik resep ini',
            ]);
        }

        $resep->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Resep telah dihapus dengan sukses',
        ]);
    }
}

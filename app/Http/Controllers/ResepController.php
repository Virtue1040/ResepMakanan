<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKatalogRequest;
use App\Http\Requests\UpdateKatalogRequest;
use Illuminate\Http\Request;
use App\Models\Katalog;

class KatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('sanctum')->user();

        $katalog = $user
            ? Katalog::where('id_user', null)->orWhere('id_user', $user->id_user)->get()
            : Katalog::where('id_user', null)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data katalog berhasil dimuat',
            'data' => $katalog->map(function ($item) use ($user) {
                return [
                    'id_katalog' => $item->id_katalog,
                    'judul' => $item->judul,
                    'manufacturer' => $item->manufacturer,
                    'harga' => $item->harga,
                    'mine' => $user && $item->id_user === $user->id_user ? "1" : "0"
                ];
            })
        ]);
    }

    /**
     * Display the image file associated with the katalog.
     */
    public function getImage($id_katalog)
    {
        $katalog = Katalog::find($id_katalog);

        if ($katalog && file_exists(public_path($katalog->imageUrl))) {
            return response()->file(public_path($katalog->imageUrl));
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gambar katalog tidak ditemukan',
        ], 404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKatalogRequest $request)
    {
        $request->validate([
            "judul" => ["required", "string", "max:255"],
            "manufacturer" => ["required", "string", "max:255"],
            "harga" => ["required", "numeric"],
            "image" => ["required", "image"],
        ]);

        $image = $request->file('image');
        $filename = 'image_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('upload'), $filename);

        Katalog::create([
            "judul" => $request->judul,
            "manufacturer" => $request->manufacturer,
            "harga" => $request->harga,
            "imageUrl" => "upload/$filename",
            "id_user" => $request->user()->id_user
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Katalog baru berhasil ditambahkan',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKatalogRequest $request, $id_katalog)
    {
        $request->validate([
            "judul" => ["required", "string", "max:255"],
            "manufacturer" => ["required", "string", "max:255"],
            "harga" => ["required", "numeric"],
            "image" => ["image"],
        ]);

        $katalog = Katalog::find($id_katalog);

        if (!$katalog) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data katalog tidak ditemukan',
            ]);
        }

        if ($katalog->id_user !== $request->user()->id_user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak diizinkan mengedit katalog ini',
            ]);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'image_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('upload'), $filename);
            $katalog->imageUrl = "upload/$filename";
        }

        $katalog->judul = $request->judul;
        $katalog->manufacturer = $request->manufacturer;
        $katalog->harga = $request->harga;
        $katalog->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Katalog berhasil diperbarui',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id_katalog)
    {
        $katalog = Katalog::find($id_katalog);

        if (!$katalog) {
            return response()->json([
                'status' => 'error',
                'message' => 'Katalog tidak ditemukan dalam sistem',
            ]);
        }

        if ($katalog->id_user !== $request->user()->id_user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk menghapus katalog ini',
            ]);
        }

        $katalog->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Katalog berhasil dihapus dari sistem',
        ]);
    }
}

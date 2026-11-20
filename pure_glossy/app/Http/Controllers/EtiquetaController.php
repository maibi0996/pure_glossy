<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EtiquetaController extends Controller
{
    public function agregar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:etiquetas,nombre',
        ], [
            'nombre.unique' => 'Esa etiqueta ya existe.',
        ]);

        DB::table('etiquetas')->insert([
            'nombre'     => trim($request->input('nombre')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Etiqueta creada correctamente.');
    }

    public function modificar(Request $request)
    {
        $idEtiqueta = $request->input('id_etiqueta');

        $request->validate([
            'id_etiqueta' => 'required|exists:etiquetas,id_etiqueta',
            'nombre'      => 'required|string|max:100|unique:etiquetas,nombre,' . $idEtiqueta . ',id_etiqueta',
        ]);

        DB::table('etiquetas')
            ->where('id_etiqueta', $idEtiqueta)
            ->update([
                'nombre'     => trim($request->input('nombre')),
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Etiqueta actualizada correctamente.');
    }

    public function borrar($id_etiqueta)
    {
        DB::table('etiquetas')->where('id_etiqueta', $id_etiqueta)->delete();

        return redirect()->back()->with('success', 'Etiqueta eliminada correctamente.');
    }
}
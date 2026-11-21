<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class listacontroller extends Controller
{
    public function mostrar(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort');

        $query = DB::table('productos');

        if (!empty($search)) {
            $busquedaLimpia = trim(preg_replace('/\s+/', ' ', mb_strtolower($search, 'UTF-8')));

            // Verificamos primero si es una coincidencia exacta por código o código de barras
            $existeExacto = DB::table('productos')
                ->where('codigo_barra', $busquedaLimpia)
                ->orWhere('codigo', $busquedaLimpia)
                ->exists();

            if ($existeExacto) {
                $query->where(function ($q) use ($busquedaLimpia) {
                    $q->where('productos.codigo_barra', $busquedaLimpia)
                        ->orWhere('productos.codigo', $busquedaLimpia);
                });
            } else {
                $palabras = array_filter(explode(' ', $busquedaLimpia));

                $query->where(function ($q) use ($palabras) {
                    foreach ($palabras as $palabra) {
                        $palabraSinTilde = str_replace(
                            ['á', 'é', 'í', 'ó', 'ú', '´'],
                            ['a', 'e', 'i', 'o', 'u', ''],
                            $palabra
                        );

                        $columnaNombreLimpia = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(productos.nombre, 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u'), '´', ''))";

                        $q->where(function ($subQ) use ($columnaNombreLimpia, $palabraSinTilde, $palabra) {
                            $subQ->where(DB::raw($columnaNombreLimpia), 'LIKE', '%' . $palabraSinTilde . '%')
                                ->orWhere('productos.codigo', 'LIKE', $palabra . '%')
                                ->orWhere('productos.codigo_barra', 'LIKE', $palabra . '%');
                        });
                    }
                });
            }
        }

        if ($sort === 'stock_asc') {
            $query->orderBy('productos.stock', 'asc');
        } elseif ($sort === 'stock_desc') {
            $query->orderBy('productos.stock', 'desc');
        }

        $productosFiltrados = $query->get();

        // Mapeo individual para evitar problemas de encoding/caracteres en joins
        $productosMapeados = [];
        foreach ($productosFiltrados as $producto) {
            $categoriaNombre = DB::table('categorias')
                ->where('id_categoria', $producto->id_categoria)
                ->value('categoria');

            $marcaNombre = DB::table('marcas')
                ->where('id_marca', $producto->id_marca ?? null)
                ->value('nombre');

            $proveedorNombre = DB::table('proveedores')
                ->where('id_proveedor', $producto->id_proveedor)
                ->value('nombre');

            $etiquetasProducto = DB::table('producto_etiqueta as pe')
                ->join('etiquetas as e', 'pe.id_etiqueta', '=', 'e.id_etiqueta')
                ->where('pe.id_producto', $producto->id_producto)
                ->select('e.id_etiqueta', 'e.nombre')
                ->orderBy('e.nombre', 'asc')
                ->get();

            $producto->categoria = $categoriaNombre ?? 'Sin categoría';
            $producto->nombre_marca = $marcaNombre ?? 'Sin marca';
            $producto->nombre_proveedor = $proveedorNombre ?? 'Sin proveedor';
            $producto->etiquetas = $etiquetasProducto->pluck('nombre')->toArray();
            $producto->etiqueta_ids = $etiquetasProducto->pluck('id_etiqueta')->toArray();

            $productosMapeados[] = $producto;
        }

        $productos = collect($productosMapeados);

        $proveedores = DB::table('proveedores')->where('estado', 'activo')->get();
        $categorias = DB::table('categorias')->get();
        $marcas = DB::table('marcas')->orderBy('nombre', 'asc')->get();
        $marcasActivas = $marcas->where('estado', 'activo');
        $etiquetas = DB::table('etiquetas')->orderBy('nombre', 'asc')->get();

        return view('lista', [
            'productos'   => $productos,
            'proveedores' => $proveedores,
            'categorias'  => $categorias,
            'marcas'      => $marcas,
            'marcasActivas' => $marcasActivas,
            'etiquetas'   => $etiquetas,
            'search'      => $search,
            'sort'        => $sort,
        ]);
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string|max:1000',
            'imagen'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'fecha_vencimiento' => 'nullable|date',
            'codigo'            => 'required|string|max:255|unique:productos,codigo',
            'codigo_barra'      => 'required|string|max:255|unique:productos,codigo_barra',
            'precio_lista'      => 'required|numeric|regex:/^\d{1,10}(\.\d{0,2})?$/',
            'precio_venta'      => 'required|numeric|regex:/^\d{1,10}(\.\d{0,2})?$/',
            'id_proveedor'      => 'nullable|int',
            'id_categoria'      => 'required|int|min:1',
            'id_marca'          => 'nullable|int',
            'etiquetas'         => 'nullable|array',
            'etiquetas.*'       => 'integer|exists:etiquetas,id_etiqueta',
            'stock'             => 'required|int',
        ], [
            'codigo.unique'       => '¡Atención! El Código ingresado ya está asignado a otro producto existente.',
            'codigo_barra.unique' => '¡Atención! El Código de Barra ingresado ya pertenece a un artículo del inventario.',
            'imagen.image'        => 'El archivo seleccionado debe ser una imagen válida.',
            'imagen.max'          => 'La imagen no debe superar los 2 MB.',
        ]);

        $rutaImagen = null;
        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')->store('productos', 'public');
        }

        DB::beginTransaction();

        try {
            $idProducto = DB::table('productos')->insertGetId([
                'nombre'            => $request->input('nombre'),
                'descripcion'       => $request->input('descripcion'),
                'imagen'            => $rutaImagen,
                'fecha_vencimiento' => $request->input('fecha_vencimiento') ?: null,
                'codigo'            => $request->input('codigo'),
                'codigo_barra'      => $request->input('codigo_barra'),
                'precio_lista'      => $request->input('precio_lista'),
                'precio_venta'      => $request->input('precio_venta'),
                'id_proveedor'      => $request->input('id_proveedor') ?: null,
                'id_categoria'      => $request->input('id_categoria'),
                'id_marca'          => $request->input('id_marca') ?: null,
                'stock'             => $request->input('stock'),
                'estado'            => 'activo',
            ]);

            $etiquetasSeleccionadas = array_unique(array_map('intval', $request->input('etiquetas', [])));
            foreach ($etiquetasSeleccionadas as $idEtiqueta) {
                DB::table('producto_etiqueta')->insert([
                    'id_producto' => $idProducto,
                    'id_etiqueta' => $idEtiqueta,
                ]);
            }

            DB::commit();

            return redirect()->route('views.lista')->with('success', 'Producto agregado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($rutaImagen) {
                Storage::disk('public')->delete($rutaImagen);
            }
            return redirect()->back()->with('error', 'No se pudo guardar el producto: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        $id_producto = $request->input('id_producto');

        $request->validate([
            'id_producto'       => 'required|exists:productos,id_producto|int',
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string|max:1000',
            'imagen'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'fecha_vencimiento' => 'nullable|date',
            'codigo'            => 'required|string|max:255|unique:productos,codigo,' . $id_producto . ',id_producto',
            'codigo_barra'      => 'required|string|max:255|unique:productos,codigo_barra,' . $id_producto . ',id_producto',
            'precio_lista'      => 'required|numeric|regex:/^\d{1,10}(\.\d{0,2})?$/',
            'precio_venta'      => 'required|numeric|regex:/^\d{1,10}(\.\d{0,2})?$/',
            'id_proveedor'      => 'nullable|int',
            'id_categoria'      => 'required|int|min:1',
            'id_marca'          => 'nullable|int',
            'etiquetas'         => 'nullable|array',
            'etiquetas.*'       => 'integer|exists:etiquetas,id_etiqueta',
            'stock'             => 'required|int',
            'estado'            => 'nullable|string|max:255',
        ], [
            'codigo.unique'       => '¡Atención! El Código ingresado ya está asignado a otro producto existente.',
            'codigo_barra.unique' => '¡Atención! El Código de Barra ingresado ya pertenece a un artículo del inventario.',
            'imagen.image'        => 'El archivo seleccionado debe ser una imagen válida.',
            'imagen.max'          => 'La imagen no debe superar los 2 MB.',
        ]);

        $productoActual = DB::table('productos')->where('id_producto', $id_producto)->first();
        $rutaImagen = $productoActual->imagen ?? null;
        $nuevaImagenSubida = null;

        if ($request->hasFile('imagen')) {
            $nuevaImagenSubida = $request->file('imagen')->store('productos', 'public');
            $rutaImagen = $nuevaImagenSubida;
        }

        DB::beginTransaction();

        try {
            DB::table('productos')
                ->where('id_producto', $id_producto)
                ->update([
                    'nombre'            => $request->input('nombre'),
                    'descripcion'       => $request->input('descripcion'),
                    'imagen'            => $rutaImagen,
                    'fecha_vencimiento' => $request->input('fecha_vencimiento') ?: null,
                    'codigo'            => $request->input('codigo'),
                    'codigo_barra'      => $request->input('codigo_barra'),
                    'precio_lista'      => $request->input('precio_lista'),
                    'precio_venta'      => $request->input('precio_venta'),
                    'id_proveedor'      => $request->input('id_proveedor') ?: null,
                    'id_categoria'      => $request->input('id_categoria'),
                    'id_marca'          => $request->input('id_marca') ?: null,
                    'stock'             => $request->input('stock'),
                    'estado'            => $request->input('estado'),
                ]);

            DB::table('producto_etiqueta')->where('id_producto', $id_producto)->delete();

            $etiquetasSeleccionadas = array_unique(array_map('intval', $request->input('etiquetas', [])));
            foreach ($etiquetasSeleccionadas as $idEtiqueta) {
                DB::table('producto_etiqueta')->insert([
                    'id_producto' => $id_producto,
                    'id_etiqueta' => $idEtiqueta,
                ]);
            }

            DB::commit();

            if ($nuevaImagenSubida && !empty($productoActual->imagen) && $productoActual->imagen !== $nuevaImagenSubida) {
                Storage::disk('public')->delete($productoActual->imagen);
            }

            return redirect()->route('views.lista', [
                'search' => $request->query('search'),
                'sort'   => $request->query('sort')
            ])->with('success', 'Producto modificado con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($nuevaImagenSubida) {
                Storage::disk('public')->delete($nuevaImagenSubida);
            }
            return redirect()->back()->with('error', 'No se pudo modificar el producto: ' . $e->getMessage());
        }
    }

    public function borrar(Request $request, $id_producto)
    {
        DB::table('productos')
            ->where('id_producto', $id_producto)
            ->update(['estado' => 'desactivado']);

        return redirect()->route('views.lista', [
            'search' => $request->query('search'),
            'sort'   => $request->query('sort')
        ])->with('success', 'Producto desactivado con éxito.');
    }

    public function aumentarPrecioPorProveedor(Request $request)
    {
        $validated = $request->validate([
            'id_proveedor' => 'required|exists:proveedores,id_proveedor',
            'porcentaje'   => 'required|numeric|min:0',
        ]);

        $porcentaje = $validated['porcentaje'];

        DB::table('productos')
            ->where('id_proveedor', $request->input('id_proveedor'))
            ->update(['precio_venta' => DB::raw('precio_venta * (1 + ' . ($porcentaje / 100) . ')')]);

        return redirect()->back()->with('success', 'Los precios se han actualizado correctamente.');
    }

    public function aumentarPreciolistaPorProveedor(Request $request)
    {
        $validated = $request->validate([
            'id_proveedor' => 'required|exists:proveedores,id_proveedor',
            'porcentaje'   => 'required|numeric|min:0',
        ]);

        $porcentaje = $validated['porcentaje'];

        DB::table('productos')
            ->where('id_proveedor', $request->input('id_proveedor'))
            ->update(['precio_lista' => DB::raw('precio_lista * (1 + ' . ($porcentaje / 100) . ')')]);

        return redirect()->back()->with('success', 'Los precios se han actualizado correctamente.');
    }
}
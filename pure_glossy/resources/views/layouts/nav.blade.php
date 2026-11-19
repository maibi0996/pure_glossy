<!-- resources/views/layouts/app.blade.php -->
@auth
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema minimercado')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pure-glossy.png') }}">
    <!-- Enlace al archivo CSS -->
    <!-- Enlace a FontAwesome y CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ventas.css') }}">
</head>


<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand navbar-dark bg-dark border-bottom border-secondary shadow-sm py-2">
    <div class="container-fluid px-3 px-md-4">

        <!-- Logo Pure Glossy a la izquierda -->
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center">
            <img src="{{ asset('images/logo-pure-glossy.png') }}"
                 alt="Pure Glossy"
                 style="height: 48px; width: auto; object-fit: contain;">
        </a>

        <!-- Perfil del usuario a la derecha -->
        <div class="d-flex align-items-center gap-2 ms-auto">
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-pill user-profile">
                <i class="fas fa-user-circle fs-5"></i>
                <div class="d-flex flex-row align-items-center gap-2">
                    <span class="fw-bold small">
                        @if(auth()->user()->rol)
                            <span class="badge badge-pure-glossy small px-2 py-1 rounded-pill text-capitalize" style="font-size: 0.7rem;">
                                {{ auth()->user()->rol }}
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

    </div>
</nav>

    <!-- Contenido principal -->
    <div class="container-fluid mt-3 main-content">
        @yield('ladoizq')
    </div>

      <!-- Contenido principal -->
      <div class="container-fluid mt-3 main-content">
        @yield('content')
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@else
    <script>window.location = "{{ route('login') }}";</script>
@endauth

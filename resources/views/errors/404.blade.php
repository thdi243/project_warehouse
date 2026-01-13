{{-- @extends('errors::minimal')

@section('title', __('Not Found'))
@section('code', '404')
@section('message', __('Not Found')) --}}


<!DOCTYPE html>
<html lang="id" class="h-full">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Halaman Tidak Ditemukan</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </head>

    <body
        class="h-full bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950 text-gray-900 dark:text-gray-100 flex items-center justify-center p-6">
        <div class="max-w-md w-full text-center space-y-8">
            <div class="mx-auto">
                <i class="fas fa-search text-blue-500 text-9xl animate-pulse"></i>
            </div>

            <h1 class="text-9xl font-black tracking-tight text-blue-600 dark:text-blue-400">404</h1>
            <h2 class="text-4xl md:text-5xl font-bold">Halaman Tidak Ditemukan</h2>

            <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 mt-6">
                {{ $exception->getMessage() ?: 'Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.' }}
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4 mt-10">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg transition transform hover:-translate-y-1">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>

                <a href="{{ url('/dashboard') }}"
                    class="inline-flex items-center px-8 py-4 bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600 text-white font-semibold rounded-xl shadow-lg transition transform hover:-translate-y-1">
                    <i class="fas fa-home mr-2"></i> Beranda
                </a>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400 mt-12">
                Coba periksa URL atau cari di menu utama.
            </p>
        </div>

        <button onclick="document.documentElement.classList.toggle('dark')"
            class="fixed bottom-6 right-6 p-3 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-full shadow-lg hover:scale-110 transition">
            <i class="fas fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:block"></i>
        </button>
    </body>

</html>

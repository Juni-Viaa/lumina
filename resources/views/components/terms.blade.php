<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan - Lumina</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

<div class="max-w-4xl mx-auto py-10 px-6">

    <div class="bg-white rounded-2xl shadow-lg p-8">

        <h1 class="text-3xl font-bold mb-6">
            Syarat & Ketentuan Penggunaan
        </h1>

        <p class="text-gray-600 mb-8">
            Terakhir diperbarui: {{ now()->format('d F Y') }}
        </p>

        <div class="space-y-6 text-gray-700 leading-8">

            <section>
                <h2 class="text-xl font-semibold mb-2">
                    1. Persetujuan Penggunaan
                </h2>

                <p>
                    Dengan membuat akun dan menggunakan Lumina, Anda menyatakan telah
                    membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang
                    berlaku pada aplikasi ini.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-2">
                    2. Tujuan Aplikasi
                </h2>

                <p>
                    Lumina merupakan sistem analisis dokumen berbasis Retrieval-Augmented
                    Generation (RAG) yang membantu pengguna memperoleh informasi dari
                    dokumen yang diunggah secara lebih cepat dan akurat.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-2">
                    3. Akun Pengguna
                </h2>

                <ul class="list-disc ml-6 space-y-2">
                    <li>Pengguna bertanggung jawab menjaga keamanan akun.</li>
                    <li>Informasi yang diberikan saat registrasi harus benar.</li>
                    <li>Dilarang menggunakan akun milik orang lain tanpa izin.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-2">
                    4. Dokumen yang Diunggah
                </h2>

                <p>
                    Pengguna bertanggung jawab atas seluruh dokumen yang diunggah ke dalam
                    sistem. Pastikan dokumen tidak melanggar hak cipta, mengandung malware,
                    atau bertentangan dengan hukum yang berlaku.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-2">
                    5. Privasi Data
                </h2>

                <p>
                    Dokumen yang diunggah hanya digunakan untuk proses analisis oleh sistem
                    dan tidak akan dibagikan kepada pihak lain tanpa izin pengguna,
                    kecuali diwajibkan oleh hukum.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-2">
                    6. Batasan Tanggung Jawab
                </h2>

                <p>
                    Hasil jawaban yang diberikan oleh sistem merupakan hasil pemrosesan
                    kecerdasan buatan berdasarkan dokumen yang tersedia. Pengguna tetap
                    bertanggung jawab untuk melakukan verifikasi terhadap informasi yang
                    diperoleh.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-2">
                    7. Perubahan Ketentuan
                </h2>

                <p>
                    Lumina berhak mengubah syarat dan ketentuan sewaktu-waktu. Perubahan
                    akan berlaku setelah dipublikasikan pada halaman ini.
                </p>
            </section>

        </div>

        <div class="mt-10 text-center">
            <a href="{{ url()->previous() }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl transition">
                Kembali
            </a>
        </div>

    </div>

</div>

</body>
</html>
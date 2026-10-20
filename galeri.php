<?php

function daftar_galeri(string $folder, string $prefiks): array
{
    $pola = __DIR__ . '/Spiderman/web/' . $folder . '/' . $prefiks . '_*';
    $hasil = [];
    foreach (glob($pola) as $berkas) {
        $hasil[] = [
            'src' => 'Spiderman/web/' . $folder . '/' . basename($berkas),
            'nama' => $prefiks,
        ];
    }
    return $hasil;
}

function label_kategori(string $prefiks): string
{
    $peta = [
        'hero_karakter' => 'Karakter',
        'hero_wallpaper' => 'Wallpaper',
        'hero_villain' => 'Villain',
        'proyek_kostum' => 'Kostum',
        'proyek_karakter' => 'Karakter',
        'proyek_villain' => 'Villain',
        'proyek_elemen' => 'Elemen',
        'aksen_wallpaper' => 'Wallpaper',
        'aksen_villain' => 'Villain',
        'aksen_elemen' => 'Elemen',
    ];
    return $peta[$prefiks] ?? ucfirst($prefiks);
}

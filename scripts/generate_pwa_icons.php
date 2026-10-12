<?php
/**
 * Script de generación de iconos PWA con emblema blanco oficial TALSA
 * Sistema Fundo — Venta de Descarte
 */

$sourceFile = __DIR__ . '/../apps/web/public/images/logo-emblem-white.png';
if (!file_exists($sourceFile)) {
    die("Error: No se encuentra el archivo fuente: $sourceFile\n");
}

$srcRaw = imagecreatefrompng($sourceFile);
imagealphablending($srcRaw, true);
imagesavealpha($srcRaw, true);

$rawW = imagesx($srcRaw);
$rawH = imagesy($srcRaw);

// Encontrar caja delimitadora exacta (bounding box) de los píxeles visibles
$minX = $rawW;
$maxX = 0;
$minY = $rawH;
$maxY = 0;

for ($y = 0; $y < $rawH; $y++) {
    for ($x = 0; $x < $rawW; $x++) {
        $c = imagecolorat($srcRaw, $x, $y);
        $alpha = ($c >> 24) & 0x7F;
        if ($alpha < 120) { // No transparente
            if ($x < $minX) $minX = $x;
            if ($x > $maxX) $maxX = $x;
            if ($y < $minY) $minY = $y;
            if ($y > $maxY) $maxY = $y;
        }
    }
}

$emblemW = $maxX - $minX + 1;
$emblemH = $maxY - $minY + 1;

echo "Emblema detectado: {$emblemW}x{$emblemH} px dentro del lienzo de {$rawW}x{$rawH} px.\n";

// Crear imagen recortada limpia del emblema
$emblem = imagecreatetruecolor($emblemW, $emblemH);
imagealphablending($emblem, false);
imagesavealpha($emblem, true);
$trans = imagecolorallocatealpha($emblem, 0, 0, 0, 127);
imagefill($emblem, 0, 0, $trans);
imagealphablending($emblem, true);

imagecopyresampled($emblem, $srcRaw, 0, 0, $minX, $minY, $emblemW, $emblemH, $emblemW, $emblemH);

/**
 * Genera icono maskable (fondo completo #166534 y emblema blanco centrado dentro de safe zone)
 */
function generateMaskableIcon($emblem, $emblemW, $emblemH, $size, $scale = 0.58) {
    $im = imagecreatetruecolor($size, $size);
    // Verde TALSA #166534 (R: 22, G: 101, B: 52)
    $bg = imagecolorallocate($im, 22, 101, 52);
    imagefill($im, 0, 0, $bg);
    imagealphablending($im, true);
    imagesavealpha($im, true);

    $targetH = round($size * $scale);
    $targetW = round($emblemW * ($targetH / $emblemH));

    $destX = round(($size - $targetW) / 2);
    $destY = round(($size - $targetH) / 2);

    imagecopyresampled($im, $emblem, $destX, $destY, 0, 0, $targetW, $targetH, $emblemW, $emblemH);
    return $im;
}

/**
 * Genera icono squircle (esquinas redondeadas sobre fondo transparente o sólido)
 */
function generateSquircleIcon($emblem, $emblemW, $emblemH, $size, $scale = 0.60) {
    $im = imagecreatetruecolor($size, $size);
    imagealphablending($im, false);
    imagesavealpha($im, true);
    $trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $trans);
    imagealphablending($im, true);

    $green = imagecolorallocate($im, 22, 101, 52);
    $radius = round($size * 0.22); // Radio suave 22%

    // Rectángulos cruzados centrales
    imagefilledrectangle($im, $radius, 0, $size - $radius - 1, $size - 1, $green);
    imagefilledrectangle($im, 0, $radius, $size - 1, $size - $radius - 1, $green);

    // 4 esquinas circulares
    imagefilledellipse($im, $radius, $radius, $radius * 2, $radius * 2, $green);
    imagefilledellipse($im, $size - $radius - 1, $radius, $radius * 2, $radius * 2, $green);
    imagefilledellipse($im, $radius, $size - $radius - 1, $radius * 2, $radius * 2, $green);
    imagefilledellipse($im, $size - $radius - 1, $size - $radius - 1, $radius * 2, $radius * 2, $green);

    // Escalar y centrar emblema
    $targetH = round($size * $scale);
    $targetW = round($emblemW * ($targetH / $emblemH));

    $destX = round(($size - $targetW) / 2);
    $destY = round(($size - $targetH) / 2);

    imagecopyresampled($im, $emblem, $destX, $destY, 0, 0, $targetW, $targetH, $emblemW, $emblemH);
    return $im;
}

$outputDir = __DIR__ . '/../apps/web/public/icons';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// 1. Iconos Maskable (Android Adaptive Icons - Full Bleed #166534)
$maskable512 = generateMaskableIcon($emblem, $emblemW, $emblemH, 512, 0.58);
$maskable192 = generateMaskableIcon($emblem, $emblemW, $emblemH, 192, 0.58);

imagepng($maskable512, "$outputDir/icon-maskable-512.png", 9);
imagepng($maskable192, "$outputDir/icon-maskable-192.png", 9);
echo "Generados: icon-maskable-512.png y icon-maskable-192.png\n";

// 2. Iconos Estándar / Squircle (Chrome Desktop, Windows, PWA Any)
$squircle512 = generateSquircleIcon($emblem, $emblemW, $emblemH, 512, 0.60);
$squircle192 = generateSquircleIcon($emblem, $emblemW, $emblemH, 192, 0.60);

imagepng($squircle512, "$outputDir/icon-512.png", 9);
imagepng($squircle192, "$outputDir/icon-192.png", 9);
echo "Generados: icon-512.png y icon-192.png\n";

// 3. Versiones WebP para máxima velocidad
if (function_exists('imagewebp')) {
    imagewebp($squircle512, "$outputDir/icon-512.webp", 95);
    imagewebp($squircle192, "$outputDir/icon-192.webp", 95);
    imagewebp($squircle512, "$outputDir/icon.webp", 95);
    echo "Generados: icon-512.webp, icon-192.webp y icon.webp\n";
}

// 4. Apple Touch Icon (180x180 - Fondo sólido #166534 para evitar fondo negro en iOS)
$appleIcon = generateMaskableIcon($emblem, $emblemW, $emblemH, 180, 0.62);
imagepng($appleIcon, __DIR__ . '/../apps/web/public/apple-touch-icon.png', 9);
echo "Generado: apple-touch-icon.png (180x180)\n";

// 5. Generar icon.svg con el emblema oficial y fondo #166534
$base64Png = base64_encode(file_get_contents("$outputDir/icon-512.png"));
$svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
  <defs>
    <linearGradient id="talsaGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#15803d"/>
      <stop offset="100%" stop-color="#14532d"/>
    </linearGradient>
  </defs>
  <!-- Fondo con esquinas redondeadas estilo PWA -->
  <rect width="512" height="512" rx="112" fill="url(#talsaGrad)"/>
  <circle cx="256" cy="256" r="190" fill="rgba(255,255,255,0.03)"/>
  <!-- Emblema oficial blanco TALSA centrado -->
  <image href="data:image/png;base64,{$base64Png}" x="0" y="0" width="512" height="512"/>
</svg>
SVG;
file_put_contents("$outputDir/icon.svg", $svgContent);
echo "Generado: icon.svg\n";

// 6. Favicon de 48x48 con emblema blanco oficial
$favicon = generateSquircleIcon($emblem, $emblemW, $emblemH, 48, 0.65);
imagepng($favicon, __DIR__ . '/../apps/web/public/favicon.ico', 9);
echo "Generado: favicon.ico (48x48)\n";

echo "¡Todos los iconos PWA fueron generados con éxito con el emblema blanco oficial!\n";

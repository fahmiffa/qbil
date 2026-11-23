<?php

// Directory for icons
$rootDir = dirname(__DIR__);
$iconsDir = $rootDir . '/public/icons';
if (!is_dir($iconsDir)) {
    mkdir($iconsDir, 0755, true);
}

// Function to draw the QBIL icon at size S x S
function generateQbilIcon($size, $isMaskable = false) {
    // Render at 4x resolution for super-sampled anti-aliasing
    $scale = 4;
    $res = $size * $scale;
    $img = imagecreatetruecolor($res, $res);
    imagealphablending($img, true);
    imagesavealpha($img, true);

    // Transparent background
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    // Colors
    // Background gradient colors (Royal Blue to Vibrant Cyan-Blue)
    $cTop = [30, 58, 138];   // #1e3a8a
    $cMid = [37, 99, 235];   // #2563eb
    $cBot = [2, 132, 199];   // #0284c7

    $cornerRadius = $isMaskable ? 0 : (int)($res * 0.22); // Rounded squircle if not maskable

    // Create a temporary canvas for background
    $bg = imagecreatetruecolor($res, $res);
    imagealphablending($bg, false);
    imagesavealpha($bg, true);

    // Vertical gradient
    for ($y = 0; $y < $res; $y++) {
        $t = $y / ($res - 1);
        if ($t < 0.5) {
            $t2 = $t * 2;
            $r = (int)($cTop[0] + ($cMid[0] - $cTop[0]) * $t2);
            $g = (int)($cTop[1] + ($cMid[1] - $cTop[1]) * $t2);
            $b = (int)($cTop[2] + ($cMid[2] - $cTop[2]) * $t2);
        } else {
            $t2 = ($t - 0.5) * 2;
            $r = (int)($cMid[0] + ($cBot[0] - $cMid[0]) * $t2);
            $g = (int)($cMid[1] + ($cBot[1] - $cMid[1]) * $t2);
            $b = (int)($cMid[2] + ($cBot[2] - $cMid[2]) * $t2);
        }
        $lineColor = imagecolorallocate($bg, $r, $g, $b);
        imageline($bg, 0, $y, $res, $y, $lineColor);
    }

    // Apply squircle mask if not maskable
    if (!$isMaskable) {
        $mask = imagecreatetruecolor($res, $res);
        imagealphablending($mask, false);
        $maskTrans = imagecolorallocatealpha($mask, 0, 0, 0, 127);
        imagefill($mask, 0, 0, $maskTrans);
        $white = imagecolorallocate($mask, 255, 255, 255);

        $r = $cornerRadius;
        // Inner rectangle
        imagefilledrectangle($mask, $r, 0, $res - $r - 1, $res - 1, $white);
        imagefilledrectangle($mask, 0, $r, $res - 1, $res - $r - 1, $white);
        // Corners
        imagefilledellipse($mask, $r, $r, $r * 2, $r * 2, $white);
        imagefilledellipse($mask, $res - $r - 1, $r, $r * 2, $r * 2, $white);
        imagefilledellipse($mask, $r, $res - $r - 1, $r * 2, $r * 2, $white);
        imagefilledellipse($mask, $res - $r - 1, $res - $r - 1, $r * 2, $r * 2, $white);

        // Apply mask to bg
        for ($x = 0; $x < $res; $x++) {
            for ($y = 0; $y < $res; $y++) {
                $alpha = (imagecolorat($mask, $x, $y) >> 24) & 0x7F;
                if ($alpha == 127) {
                    imagesetpixel($bg, $x, $y, $transparent);
                }
            }
        }
        imagedestroy($mask);
    }

    // Copy bg to main img
    imagecopy($img, $bg, 0, 0, 0, 0, $res, $res);
    imagedestroy($bg);

    // Now draw the QBIL logo in the center
    // Logo viewBox is 24x24
    // We scale it into a central bounding box
    // For maskable, safe zone is inner 65%; for regular, 60%
    $contentRatio = $isMaskable ? 0.52 : 0.58;
    $contentSize = $res * $contentRatio;
    $offsetX = ($res - $contentSize) / 2;
    $offsetY = ($res - $contentSize) / 2;

    $scaleCoord = function($v) use ($contentSize, $offsetX, $offsetY) {
        return $offsetX + ($v / 24) * $contentSize;
    };

    $whiteColor = imagecolorallocate($img, 255, 255, 255);
    $cyanAccent = imagecolorallocate($img, 147, 197, 253); // #93c5fd light blue accent
    $strokeWidth = max(2, (int)($res * 0.045));

    // Thick stroke drawing helper for lines
    $drawThickLine = function($im, $x1, $y1, $x2, $y2, $color, $weight) {
        $t = $weight / 2;
        $angle = atan2($y2 - $y1, $x2 - $x1);
        $perp = $angle + M_PI_2;
        $dx = cos($perp) * $t;
        $dy = sin($perp) * $t;
        $pts = [
            $x1 + $dx, $y1 + $dy,
            $x2 + $dx, $y2 + $dy,
            $x2 - $dx, $y2 - $dy,
            $x1 - $dx, $y1 - $dy,
        ];
        imagefilledpolygon($im, $pts, $color);
        imagefilledellipse($im, (int)$x1, (int)$y1, (int)$weight, (int)$weight, $color);
        imagefilledellipse($im, (int)$x2, (int)$y2, (int)$weight, (int)$weight, $color);
    };

    // The logo features:
    // 1. Arc: circle at (12, 12), radius 10 (diameter 20), from ~45 deg to 360 deg
    // SVG path: M20.34 17.52 a10 10 0 1 0 -2.82 2.82
    // Let's draw the arc using high precision sample dots or thick arc
    $cx = $scaleCoord(12);
    $cy = $scaleCoord(12);
    $radius = (10 / 24) * $contentSize;

    // Angle of (20.34, 17.52) relative to (12,12) is atan2(5.52, 8.34) = ~33.5 degrees
    // SVG arc goes from (20.34, 17.52) counter-clockwise (or clockwise in SVG coords) around to (17.52, 20.34)
    $startAngle = atan2(17.52 - 12, 20.34 - 12); // ~0.585 rad (~33.5 deg)
    $endAngle = atan2(20.34 - 12, 17.52 - 12);   // ~1.0 rad (~56.5 deg)
    // Span is from endAngle clockwise all the way around to startAngle (about 337 degrees)
    $totalSteps = 600;
    $span = (2 * M_PI) - ($endAngle - $startAngle);
    for ($i = 0; $i <= $totalSteps; $i++) {
        $a = $endAngle + ($i / $totalSteps) * $span;
        $px = $cx + cos($a) * $radius;
        $py = $cy + sin($a) * $radius;
        imagefilledellipse($img, (int)$px, (int)$py, (int)$strokeWidth, (int)$strokeWidth, $whiteColor);
    }

    // 2. Line: from (13.41, 13.41) to (17.59, 17.59)
    $x1 = $scaleCoord(13.41);
    $y1 = $scaleCoord(13.41);
    $x2 = $scaleCoord(17.59);
    $y2 = $scaleCoord(17.59);
    $drawThickLine($img, $x1, $y1, $x2, $y2, $whiteColor, $strokeWidth);

    // 3. Node circle at (19, 19), radius 2 in 24x24 coords (diameter 4)
    $node1X = $scaleCoord(19);
    $node1Y = $scaleCoord(19);
    $node1D = (4.5 / 24) * $contentSize;
    imagefilledellipse($img, (int)$node1X, (int)$node1Y, (int)$node1D, (int)$node1D, $cyanAccent);
    // Ring border
    // imageellipse with thickness:
    for ($w = 0; $w < $strokeWidth * 0.7; $w++) {
        imageellipse($img, (int)$node1X, (int)$node1Y, (int)($node1D + $w), (int)($node1D + $w), $whiteColor);
    }

    // 4. Center node circle at (12, 12), radius 2 in 24x24 coords (diameter 4)
    $node2X = $scaleCoord(12);
    $node2Y = $scaleCoord(12);
    $node2D = (4.5 / 24) * $contentSize;
    imagefilledellipse($img, (int)$node2X, (int)$node2Y, (int)$node2D, (int)$node2D, $whiteColor);

    // Now downsample from $res to $size
    $final = imagecreatetruecolor($size, $size);
    imagealphablending($final, false);
    imagesavealpha($final, true);
    imagecopyresampled($final, $img, 0, 0, 0, 0, $size, $size, $res, $res);

    imagedestroy($img);
    return $final;
}

$sizes = [
    72 => 'icon-72x72.png',
    96 => 'icon-96x96.png',
    128 => 'icon-128x128.png',
    144 => 'icon-144x144.png',
    152 => 'icon-152x152.png',
    180 => 'apple-touch-icon.png',
    192 => 'icon-192x192.png',
    384 => 'icon-384x384.png',
    512 => 'icon-512x512.png',
    32 => 'favicon-32x32.png',
    16 => 'favicon-16x16.png',
];

echo "Generating standard icons...\n";
foreach ($sizes as $size => $name) {
    $im = generateQbilIcon($size, false);
    imagepng($im, $iconsDir . '/' . $name, 9);
    imagedestroy($im);
    echo "Generated {$name} ({$size}x{$size})\n";
}

echo "Generating maskable icons...\n";
$maskableSizes = [
    192 => 'icon-maskable-192x192.png',
    512 => 'icon-maskable-512x512.png',
];
foreach ($maskableSizes as $size => $name) {
    $im = generateQbilIcon($size, true);
    imagepng($im, $iconsDir . '/' . $name, 9);
    imagedestroy($im);
    echo "Generated {$name} ({$size}x{$size} maskable)\n";
}

// Copy apple-touch-icon to public/apple-touch-icon.png and public/favicon.png
copy($iconsDir . '/apple-touch-icon.png', $rootDir . '/public/apple-touch-icon.png');
copy($iconsDir . '/favicon-32x32.png', $rootDir . '/public/favicon.ico');
copy($iconsDir . '/icon-192x192.png', $rootDir . '/public/icon.png');

echo "All icons successfully generated!\n";

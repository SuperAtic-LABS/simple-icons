<?php

$icons = json_decode(file_get_contents('icons.json'), true)["icons"];

foreach($icons as $key => $icon) {

    $hex = $icons[$key]["colour"];

    $icons[$key]["red"] = base_convert(substr($hex, 0, -4), 16, 10);
    $icons[$key]["green"] = base_convert(substr($hex, 2, -2), 16, 10);
    $icons[$key]["blue"] = base_convert(substr($hex, 4), 16, 10);
    
    $red = $icons[$key]["red"] / 255;
    $green = $icons[$key]["green"] / 255;
    $blue = $icons[$key]["blue"] / 255;

    $maxRGB = max($red, $green, $blue);
    $minRGB = min($red, $green, $blue);
    $delta = $maxRGB - $minRGB;

    $lightness = 100 * ($maxRGB + $minRGB) / 2;

    if ($delta == 0) {
        $saturation = 0;
        $hue = 0;
    } else {
        $saturation = 100 * $delta / (1 - abs((2 * ($lightness / 100)) - 1));
        
        if ($maxRGB == $red) {
            $hue = (($green - $blue) / $delta);
            $hue *= 60;
            if ($hue < 0) {
                $hue += 360;
            }
        } else if ($maxRGB == $green) {
            $hue = ((($blue - $red) / $delta) + 2);
            $hue *= 60;
        } else {
            $hue = ((($red - $green) / $delta) + 4);
            $hue *= 60;
        }
    }

    $icons[$key]["hue"] = round($hue);
    $icons[$key]["saturation"] = round($saturation);
    $icons[$key]["lightness"] = round($lightness);
    
    $icons[$key]["modHue"] = ($icons[$key]["hue"] + 119) % 360;
}

$name = array();
$modhue = array();
$saturation = array();
$lightness = array();
foreach ($icons as $key => $icon) {
    $name[$key] = $icon["name"];
    $modhue[$key] = $icon["modHue"];
    $saturation[$key] = $icon["saturation"];
    $lightness[$key] = $icon["lightness"];
}
array_multisort($modhue, SORT_DESC, $saturation, SORT_DESC, $lightness, SORT_DESC, $name, SORT_ASC, $icons);

for ($i=0; $i < count($icons); $i++) {
    if ($icons[$i]["saturation"] <= 15) {
        $greyIcon = $icons[$i];
        unset($icons[$i]);
        array_push($icons, $greyIcon);
    }
}

$markup = file_get_contents('header.html');

foreach($icons as $icon) {
    $fileName = "./icons/" . $icon["filename"] . ".svg";
    $fileContent = file_get_contents($fileName);
    $markup = $markup . "\t<li>" . $fileContent . "</li>\n";
}

$markup = $markup . "</ul>";

$file = fopen('index.html', 'w');
fwrite($file, $markup);
fclose($file);

















?>
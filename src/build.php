<?php

//// Get information about icons
	
	// Build array of icons from simple-icons.json
	echo "Creating array from remote JSON file.";
	$json = file_get_contents('https://github.com/danleech/simple-icons/raw/master/simple-icons.json');
	$icons = json_decode($json, true);
	
	
	
//// Add HSL values to the icons array
	
	echo "\nAdding HSL values to icon array.\n";
	foreach($icons as $key => $icon) {
	
		// Get hex value from array
		$hex = $icons[$key]["hex"];

		// Generate RGB values
		$icons[$key]["red"] = base_convert(substr($hex, 0, -4), 16, 10);
		$icons[$key]["green"] = base_convert(substr($hex, 2, -2), 16, 10);
		$icons[$key]["blue"] = base_convert(substr($hex, 4), 16, 10);

		// Set RGB variables
		$red = $icons[$key]["red"] / 255;
		$green = $icons[$key]["green"] / 255;
		$blue = $icons[$key]["blue"] / 255;
	
		// Calculate max and min RGB values, and the delta of those values
		$maxRGB = max($red, $green, $blue);
		$minRGB = min($red, $green, $blue);
		$delta = $maxRGB - $minRGB;
	
		// Calculate lightness
		$lightness = 100 * ($maxRGB + $minRGB) / 2;
		
		// Check to see if the colour is grey
		if ($delta == 0) {
			$saturation = 0;
			$hue = 0;
		} else {
			
			// Calculate saturation
			$saturation = 100 * $delta / (1 - abs((2 * ($lightness / 100)) - 1));
			
			// Calculate hue
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
		
		// Rotate hue 119 degrees so the list starts with a blue icon
		$icons[$key]["modHue"] = ($icons[$key]["hue"] + 119) % 360;
		
		echo ".";
	}
	
	
	
//// Sort array
	
	// Sort by hue, saturation, lightness then name
	echo "\nSorting array.";
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
	
	// Move greys (saturation < 15) to the end
	echo "\nMoving greys to end of array.";
	for ($i=0; $i < count($icons); $i++) {
		if ($icons[$i]["saturation"] <= 15) {
			
			// If item is grey, remove it from the array and add to the end
			$greyIcon = $icons[$i];
			unset($icons[$i]);
			array_push($icons, $greyIcon);
		}
	}
	
	
	
//// Build markup for icons

	// Open an unordered list
	$iconsMarkup = "\t\t<ul class=\"icons\">\n";
	
	// Create a list item and associated LESS class per icon
	echo "\nBuilding markup for icons.\n";
	$i = 0;
	foreach($icons as $icon) {
		
		// Get 'code safe' name, i.e. lowercase with no spaces, dots or bangs
		$iconCodeName = strtolower($icon["name"]);
		$iconCodeName = str_replace(" ", "", $iconCodeName);
		$iconCodeName = str_replace(".", "", $iconCodeName);
		$iconCodeName = str_replace("!", "", $iconCodeName);
		
		// Get URLs
		$repoURL = "https://github.com/danleech/simple-icons/tree/master/icons/" . $iconCodeName . "/";
		$iconURL = "https://raw.github.com/danleech/simple-icons/master/icons/" . $iconCodeName . "/" . $iconCodeName . "-64.png";
		$retinaURL = "https://raw.github.com/danleech/simple-icons/master/icons/" . $iconCodeName . "/" . $iconCodeName . "-128.png";
		
		// Build line item
		$iconsMarkupArray[$i] = "\t\t\t<li><a href=\"" . $repoURL . "\" title=\"" . $icon["name"] . " – #" . $icon["hex"] . "\" class=\"si-" . $iconCodeName . "\">" . $icon["name"] . "</a></li>\n";
		
		// Base64 encode images
		$iconFile = file_get_contents($iconURL);
		$iconBase64 = base64_encode($iconFile);
		$retinaIconFile = file_get_contents($retinaURL);
		$retinaBase64 = base64_encode($retinaIconFile);
		
		// Build stylesheet lines
		$stylesheetContentArray[$i] = ".si-" . $iconCodeName . "{background-color:#" . $icon["hex"] . ";background-image:url(data:image/png;base64," . $iconBase64 . ")}";
		$stylesheetRetinaArray[$i] = ".si-" . $iconCodeName . "{background-image:url(data:image/png;base64," . $retinaBase64 . ")}";
		
		$i++;
		echo ".";
	}
	
	// Build one long string from all the line items
	echo "\nBuilding markup string.\n";
	foreach($iconsMarkupArray as $icon) {
		$iconsMarkup = $iconsMarkup . $icon;
		echo ".";
	}
	echo "\nBuilding stylesheet string.\n";
	foreach($stylesheetContentArray as $icon) {
		$stylesheetContent = $stylesheetContent . $icon;
		echo ".";
	}
	echo "\nBuilding retina stylesheet string.\n";
	foreach($stylesheetRetinaArray as $icon) {
		$retinaContent = $retinaContent . $icon;
		echo ".";
	}
	
	// Close the unordered list
	$iconsMarkup = $iconsMarkup . "\t\t</ul>";
	
	
	
//// Build markup for colour table
	
	// Sort alphabetically
	echo "\nSorting alphabetically.";
	$name = array();
	foreach ($icons as $key => $icon) {
		$name[$key] = strtoupper($icon["name"]);
	}
	array_multisort($name, SORT_ASC, $icons);
	
	// Build table header
	echo "\nBuilding table.\n";
	$tableMarkup = "\n\t\t<table class=\"colour-table\">\n";
	$tableMarkup = $tableMarkup . "\t\t\t<thead>\n";
	$tableMarkup = $tableMarkup . "\t\t\t\t<tr>\n";
	$tableMarkup = $tableMarkup . "\t\t\t\t\t<th>Icons (" . count($icons) .")</th>\n";
	$tableMarkup = $tableMarkup . "\t\t\t\t\t<th>HEX Value</th>\n";
	$tableMarkup = $tableMarkup . "\t\t\t\t\t<th>RGB Values</th>\n";
	$tableMarkup = $tableMarkup . "\t\t\t\t\t<th>HSL Values</th>\n";
	$tableMarkup = $tableMarkup . "\t\t\t\t</tr>\n";
	$tableMarkup = $tableMarkup . "\t\t\t</thead>\n";
	$tableMarkup = $tableMarkup . "\t\t\t<tbody>\n";
	
	// Build table rows
	foreach($icons as $key => $icon) {
		$tableMarkup = $tableMarkup . "\t\t\t\t<tr>\n";
		$tableMarkup = $tableMarkup . "\t\t\t\t\t<td>" . $icon["name"] . "</td>\n";
		$tableMarkup = $tableMarkup . "\t\t\t\t\t<td class=\"colour-value\">#" . $icon["hex"] . "</td>\n";
		$tableMarkup = $tableMarkup . "\t\t\t\t\t<td class=\"colour-value\">rgb(" . $icon["red"] . "," . $icon["green"] . "," . $icon["blue"] . ")</td>\n";
		$tableMarkup = $tableMarkup . "\t\t\t\t\t<td class=\"colour-value\">hsl(" . $icon["hue"] . "," . $icon["saturation"] . "," . $icon["lightness"] . ")</td>\n";
		$tableMarkup = $tableMarkup . "\t\t\t\t</tr>\n";
		echo ".";
	}
	
	// Close table markup
	$tableMarkup = $tableMarkup . "\t\t\t</tbody>\n";
	$tableMarkup = $tableMarkup . "\t\t</table>\n";
	
	
	
//// Build sourceOutput

	// Add all the bits of markup together
	echo "\n Building HTML.";
	$outputMarkup= file_get_contents('header.html') . $iconsMarkup . $tableMarkup . file_get_contents('footer.html');
	
	// Minify using regex voodoo from http://stackoverflow.com/questions/5312349
	echo "\n Minifying HTML.";
    $outputMarkup = preg_replace('%(?>[^\S ]\s*|\s{2,})(?=[^<]*+(?:<(?!/?(?:textarea|pre|script)\b)[^<]*+)*+(?:<(?>textarea|pre|script)\b|\z))%Six', "", $outputMarkup);
	
	
//// Build Stylesheet
	echo "\n Building stylesheet.";
	$stylesheet = $stylesheetContent;
	echo "\n Building retina stylesheet.";
	$retinaStylesheet = $retinaContent;



//// Generate files
	
	// Write markup to index.html
	echo "\n Writing HTML.";
	$file = fopen('index.html', 'w');
	fwrite($file, $outputMarkup);
	fclose($file);
	
	// Write stylesheet to icons.less (this will get imported into master.css by CodeKit)
	echo "\n Writing stylesheet.";
	$file = fopen('icons.less', 'w');
	fwrite($file, $stylesheet);
	fclose($file);
	
	// Write retina stylesheet to css/retina.css
	echo "\n Writing retina stylesheet.";
	$file = fopen('css/retina.css', 'w');
	fwrite($file, $retinaStylesheet);
	fclose($file);



//// Output a completed message
	
	// Display a link to index.html
	echo "\nDone. " . count($icons) . " icons.\n";
	
?>
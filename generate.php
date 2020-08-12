<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// load user data/settings from file 
$objs = yaml_parse_file("pages.txt");

$prefs = $objs["settings"];
$common = $objs["common"];
$pages = $objs["pages"];

// construct page from template
$main = file_get_contents("template/main.html");
$header = file_get_contents("template/header.html");
$footer = file_get_contents("template/footer.html");
$main = str_replace("%HEADER%", $header, $main);
$main = str_replace("%FOOTER%", $footer, $main);

// insert page title
$main = str_replace("%TITLE%", default_if_nonexisting("title", $common, "TITLE"), $main);

// insert home page, imprint and privacy URLs
$main = str_replace("%HOME%", default_if_nonexisting("home", $common, "/"), $main);
$main = str_replace("%IMPRINT%", default_if_nonexisting("imprint", $common, "/"), $main);
$main = str_replace("%PRIVACY%", default_if_nonexisting("privacy", $common, "/"), $main);

default_if_nonexisting("output_folder", $prefs, "www/");
$outputPath = sanitize_path($prefs["output_folder"]);

$imagePath = default_if_nonexisting("image_path", $prefs, "images");
$audioPath = default_if_nonexisting("audio_path", $prefs, "audio");

foreach($pages as $key => $obj) {
    echo "Processing ".$key.": ";
    if (!is_data_valid($obj)) {
        echo "Invalid data! Skipping. \n";
        continue;
	}
	
    $fileTmp = $main;
	$imagefile = "";
	
	// generate info (i.e., images/audio and description)
	$info["info"] = "";
	$info["links"] = "";
	if (array_key_exists("info", $obj)) {
		$info = process_info($obj["info"], $imagePath, $audioPath);
	}
	
	// generate the navigation (svg-based "imagemap")
	$navigation = "";
	if (array_key_exists("navigation", $obj)) {
		$navigation = process_navigation($obj["navigation"]);;
	}
	
	// replace placeholders by actual content
	$fileTmp = str_replace("%WIDTH%", $obj["width"], $fileTmp);
	$fileTmp = str_replace("%HEIGHT%", $obj["height"], $fileTmp);
	$fileTmp = str_replace("%IMAGEFILE%", $imagePath.$obj["image"], $fileTmp);
	
	$fileTmp = str_replace("%INFOS%", $info["info"], $fileTmp);
	$fileTmp = str_replace("%LINKS%", $info["links"], $fileTmp);
	
	$fileTmp = str_replace("%NAVIGATION%", $navigation, $fileTmp);
	
	// write content to file
	$fname = sanitize_path($key);
	$file = @fopen($outputPath.$fname.".html", "w");
	if ($file === false) {
        echo "Error opening ".$outputPath.$fname.".html !\n";
        continue;
	}
	fwrite($file, $fileTmp);
	fclose($file);
	
	echo "Done! \n";
}


echo "Generating pages finished.\n";
echo "Total pages: ".(count($pages))."\n";




function process_info ($obj, $imagePath, $audioPath){
	$template[1][1] = file_get_contents("template/popup_audio_image.html");
	$template[1][0] = file_get_contents("template/popup_audio.html");
	$template[0][1] = file_get_contents("template/popup_image.html");
	$template[0][0] = file_get_contents("template/popup.html");
	$linkTemplate 	= file_get_contents("template/link.html");
	
	$info = "";
	$links = "";
	foreach($obj as $key => $props) {
        default_if_nonexisting("left", $props, 0);
		default_if_nonexisting("top", $props, 0);
		default_if_nonexisting("width", $props, 100);
		default_if_nonexisting("height", $props, 100);
		default_if_nonexisting("title", $props, "TITLE");
		default_if_nonexisting("description", $props, "DESCRIPTION");
		
		if (array_key_exists("audio", $props)) {
			$audio = 1;
		} else {
			$audio = 0;
		}
		
		if (array_key_exists("image", $props)) {
			$image = 1;
		} else {
			$image = 0;
		}
		
		// Popup info
		$tmp = $template[$audio][$image];
		
		$tmp = str_replace("%ITEM%", $key, $tmp);
		$tmp = str_replace("%TITLE%", $props["title"], $tmp);
		$tmp = str_replace("%DESC%", $props["description"], $tmp);
		
		if ($image == 1) {
			$tmp = str_replace("%IMAGEFILE%", $imagePath.$props["image"], $tmp);
		}
		
		if ($audio == 1) {
			$tmp = str_replace("%AUDIOFILE%", $audioPath.$props["audio"], $tmp);
		}
		
		$info = $info . $tmp;
		
		// Links
		$tmp = $linkTemplate;
		
		$tmp = str_replace("%ITEM%", $key, $tmp);
		$tmp = str_replace("%LEFT%", $props["left"], $tmp);
		$tmp = str_replace("%TOP%", $props["top"], $tmp);
		$tmp = str_replace("%WIDTH%", $props["width"], $tmp);
		$tmp = str_replace("%HEIGHT%", $props["height"], $tmp);
				
		$links = $links . $tmp;
	}
	
	$outp["info"] = $info;
	$outp["links"] = $links;
	return $outp;
}

function process_navigation ($obj){
	$dirIt = new DirectoryIterator("template/svg/");
	
	$nav["INVALID"] = "";
	foreach ($dirIt as $fInfo) {
		if ($fInfo->isDot()) {
			continue;
		}
		$fName = $fInfo->getFilename();
		$key = stristr($fName, ".svg_part", true); // strip extension
		
		$nav[$key] = file_get_contents("template/svg/".$fName);
	}
	
	$template = file_get_contents("template/nav_link.html");
	

	$navigation = "";
	foreach($obj as $key => $props) {
		default_if_nonexisting("left", $props, 0);
		default_if_nonexisting("top", $props, 0);
		default_if_nonexisting("width", $props, 100);
		default_if_nonexisting("height", $props, 100);
		default_if_nonexisting("url", $props, "index.html");
		default_if_nonexisting("type", $props, "INVALID");
		
		$tmp = $template;
		$tmp = str_replace("%URL%", $props["url"], $tmp);
		$tmp = str_replace("%SVG%", $nav[$props["type"]], $tmp);
		
		$tmp = str_replace("%LEFT%", $props["left"], $tmp);
		$tmp = str_replace("%TOP%", $props["top"], $tmp);
		$tmp = str_replace("%WIDTH%", $props["width"], $tmp);
		$tmp = str_replace("%HEIGHT%", $props["height"], $tmp);
		
		$navigation = $navigation . $tmp;
	}
	
	return $navigation;
}
 
function sanitize_path ($key) {
	return preg_replace("[^\w\-]", "", $key);
}

function is_data_valid($obj) {
    $error = false;
    if (!array_key_exists("width", $obj)) {
        echo "width unspecified\n";
        $error = true;
    } else {
        if ($obj["width"] <= 0) {
            echo "invalid width\n";
            $error = true;
        }
    }
    
    if (!array_key_exists("height", $obj)) {
        echo "height unspecified\n";
        $error = true;
    } else {
        if ($obj["height"] <= 0) {
            echo "invalid height\n";
            $error = true;
        }
    }
    
    if (!array_key_exists("image", $obj)) {
        echo "image unspecified\n";
        $error = true;
    }
    
    return !$error;
}

function default_if_nonexisting ($key, &$array, $default) {
    if (!array_key_exists($key, $array)) {
        $array[$key] = $default;
    }
    
    return $array[$key];
}

?>

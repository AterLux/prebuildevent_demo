<?php

  if ($_SERVER['argc'] < 3) die("ERROR: Use: php -f " . $_SERVER['argv'][0] . " <dir_with_bmps> <dest_c_file>\r\n");
  $src_dir = $_SERVER['argv'][1];
  $dest_file = $_SERVER['argv'][2];

  if (!is_dir($src_dir)) die("ERROR: Directory $src_dir does not exist\r\n");

  $files = array();

  // Проверим дату изменения файла, если она равна максимальной дате исходных файлов, то картинки не менялись
  $maxmtime = filemtime($_SERVER["SCRIPT_FILENAME"]); // Учитываем дату изменения скрипта. Если он тоже менялся, то перестроим картинки

  $dirmtime = filemtime($src_dir); // Если дата директории менялась то тоже перегенерируем
  if ($dirmtime > $maxmtime) $maxmtime = $dirmtime;

  $dh = opendir($src_dir) or die("ERROR: Cannot open directory $src_dir\r\n");
  while (($file = readdir($dh)) !== false) {
    $fn = $src_dir . '/' . $file;
    if (is_file($fn) && preg_match('/([A-Za-z0-9_]+)\.bmp/', $file, $r)) {
      $tm = filemtime($fn);
      if ($tm > $maxmtime) $maxmtime = $tm;
      $files[$r[1]] = $fn;
    }
  }
  closedir($dh);

  // Если исходные файлы не менялись - выходим
  if (file_exists($dest_file) && (filemtime($dest_file) == $maxmtime)) die("Images were not modified\r\n");


  print("Converting " . count($files) . " images into $dest_file...\r\n");

  function closedie($message) {
    global $f;
    if ($f) fclose($f);
    return die($message . "\r\n");
  } 

  $images = array();

  foreach ($files as $code => $src_file) {
    // Начитываем BMP файл
    $f = fopen($src_file, 'rb') or die("ERROR: cannot open file $src_file\r\n");

    $header_data = fread($f, 54);
    if (strlen($header_data) < 54) die('ERROR! Error reading file header');

    $header = unpack('vtype/Vsize/Vreserved/Voffbits/Vinfosize/Vwidth/Vheight/vplanes/vbitcount/Vcompression/Vsizeimage/Vxppm/Vyppm/Vcirused/Vcirimportant', $header_data);

    if ($header['type'] != 0x4D42) closedie('ERROR: Unsupported file format');
    if ($header['infosize'] < 40) closedie('ERROR: Unsupported header type');
    if ($header['bitcount'] != 24) closedie('ERROR: Unsupported pixel format (24 bits per pixel expected)');
    if ($header['compression'] != 0) closedie('ERROR: Unsupported compression type (plain RGB expected)');

    $width = $header['width'];
    $height = $header['height'];

    if (($width < 8) || ($height < 8) || ($width > 1024) || ($height > 1024)) closedie('ERROR! Unsupported bitmap size: ' . $width . ' x ' . $height);

    $bits_offset = $header['offbits'];

    // Количество байт в строках выровнено крато 4 байтам.
    $bytes_in_row = ($width * 3) + ($width % 4);

    // Размеры выходной картинки установим жёстко
    $pages = 8; // (int)ceil($height / 8); 
    $target_width = 128;
    $target_height = $pages * 8;

    // Подготовим двумерный массив пикселей
    $pixels = array_fill(0, $target_height, array_fill(0, $target_width, 0.0));

    $blocks_data = array();
    $blocks_count = 0;
    $blocks_top = 0;

    $fonts_data = array();
    $max_illum = 0.1;
    $use_width = ($target_width > $width) ? $width : $target_width;

    // Грузим картинку
    for ($y = (($target_height > $height) ? $height : $target_height) - 1 ; $y >= 0; $y--) {
      fseek($f, $bits_offset + (($height - 1 - $y) * $bytes_in_row));
      $row_data = fread($f, $width * 3);

      $pix_line = '';
      $i = 0;
      for ($x = 0 ; $x < $use_width ; $x++) {
        $b = ord($row_data[$i++]) / 255.0;
        $g = ord($row_data[$i++]) / 255.0;
        $r = ord($row_data[$i++]) / 255.0;
//        $illum = sqrt(0.2126 * $r * $r + 0.7152 * $g * $g + 0.0722 * $b * $b); // Вычисление с учётом гаммы
        $illum = 0.3 * $r + 0.69 * $g + 0.11 * $b; // Упрощённый вариант. Результат примерно тот же
        if ($illum > $max_illum) $max_illum = $illum;
        $pixels[$y][$x] = $illum;
      }
    } // for ($y ...)

    fclose($f);

    $illum_threshold = $max_illum * 0.5;

    // Пускаем Флойда-Штайнберга
    for ($y = 0 ; $y < $target_height ; $y++) {
      // Каждую вторую строку прохим в обратном направлении
      if (($y & 1) == 0) {
        for ($x = 0 ; $x < $target_width ; $x++) {
          $inputval = $pixels[$y][$x];
          $outputval = ($inputval >= $illum_threshold) ? $max_illum : 0;
          $error = $outputval - $inputval;
          if (($x + 1) < $target_width) $pixels[$y][$x + 1] -= $error * (7.0 / 16.0);
          $y1 = $y + 1;
          if ($y1 < $target_height) {
            if ($x > 0) $pixels[$y1][$x - 1] -= $error * (3.0 / 16.0);
            $pixels[$y1][$x] -= $error * (5.0 / 16.0);
            if (($x + 1) < $target_width) $pixels[$y1][$x + 1] -= $error * (1.0 / 16.0);
          }
        }
      } else {
        for ($x = $target_width - 1 ; $x >= 0 ; $x--) {
          $inputval = $pixels[$y][$x];
          $outputval = ($inputval >= $illum_threshold) ? $max_illum : 0;
          $error = $outputval - $inputval;
          if ($x > 0) $pixels[$y][$x - 1] -= $error * (7.0 / 16.0);
          $y1 = $y + 1;
          if ($y1 < $target_height) {
            if ($x > 0) $pixels[$y1][$x - 1] -= $error * (1.0 / 16.0);
            $pixels[$y1][$x] -= $error * (5.0 / 16.0);
            if (($x + 1) < $target_width) $pixels[$y1][$x + 1] -= $error * (3.0 / 16.0);
          }
        }
      }
    } // for ($y ...)

    // Кодируем картинку
    $image = array();
    for ($p = 0 ; $p < $pages ; $p++) {
      $row = '';
      for ($x = 0 ; $x < $width ; $x++) {
        $yp = ($p + 1) * 8;
        $d = 0;
        for ($y = $yp - 8 ; $y < $yp ; $y++) {
          $d >>= 1;
          if ($pixels[$y][$x] >= $illum_threshold) $d |= 0x80;
        }
        $row .= chr($d);
      }
      $image[] = $row;
    }
    $images[$code] = $image;
  } // foreach ($files ...)

  // Генерируем код на C
  $fout = fopen($dest_file, 'w') or die("ERROR: Cannot write file $dest_file\r\n");

  fputs($fout, 
"// This file is generated automatically
// Source files max timestamp " . date('j.n.Y H:i:s', $maxmtime) ."
// Generated at " . date('j.n.Y H:i:s') . "

");

  $all_img = '';

  foreach ($images as $code => &$image) {
     $pages = count($image);
     $width = strlen($image[0]);

     $text = "const PROGMEM uint8_t img_${code}[$pages][$target_width] = {\r\n";
     $is_first = true;
     foreach ($image as &$page) {
       if ($is_first) $is_first = false; else $text .= ",\r\n";
       $text .= "  { ";
       for ($x = 0 ; $x < $width ; $x++) {
         if ($x > 0) {
           $text .= ', ';
           if (($x % 16) == 0) $text .= "\r\n    ";
         }
         $text .= '0x'.str_pad(strtoupper(dechex(ord($page[$x]))), 2, '0', STR_PAD_LEFT);
       }
       $text .= " }";
     }
     $text .= "\r\n}; // img_$code\r\n\r\n";
     fputs($fout, $text);

     if ($all_img != '') $all_img .= ", ";
     $all_img .= " &img_$code";
/*
     $fo = fopen($code . '.raw', 'wb');
     foreach ($image as &$page) fwrite($fo, $page);
     fclose($fo);/**/
  } // foreach ($images ...)

  fputs($fout, "const PROGMEM PGM_VOID_P const list_img[] = {\r\n    $all_img\r\n  };\r\n\r\n" );

  fclose($fout);

  touch($dest_file, $maxmtime);

  print("$dest_file successfully generated.\r\n");

?>
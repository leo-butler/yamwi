<?php
$mode = 2; // 0 -> ASCII-Art output,
           // 1 -> Binary TeX output,
           // 2 -> Enhanced ASCII-Art output,
           // 3 -> Syntactic output
           // 4 -> Remote TeX + MathJax
	       // 5 -> MathML

$mode1is4 = false;  // Set to true to dis-able Binary TeX output

$max_file_time = 1;

$max_process_time = 120;

$max_num_processes = 30;

$timelimit_binary = '/usr/bin/timeout';

$maxima_binary = '/usr/bin/maxima';

$maxima_args = "";

$gnuplot_command = '/usr/bin/gnuplot';

$magic_key = "1dc53ea6b0ae1e618fc4e123238192b"; // CHANGE THIS!!

$movie_muxer = 'webm'; // needs to be one of webm, mp4 (ogg does not work)

$movie_is_embedded = 0; // webm can be 0 or 1; mp4 must be 0.

$ffmpeg_bin = '/usr/bin/ffmpeg';

$base64_cmd = '/usr/bin/base64 -w 0';

//////////////
// MESSAGES //
//////////////


$message_dangerous = "Yamwi detected forbidden code: ";
$message_time_process = "Requested process aborted. It exceeded maximum execution time.";
$message_too_many_processes = "Too many users. Please, try later.";
$message_prog_error = "Programming error detected. Check your input.";
$submit_button = "Submit";
$clear_button = "Clear";

?>

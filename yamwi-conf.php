<?php
$mode = 2; // 0 -> ASCII-Art output,
           // 1 -> Binary TeX output,
           // 2 -> Enhanced ASCII-Art output,
           // 3 -> Syntactic output
           // 4 -> Remote TeX + MathJax
	       // 5 -> MathML

$mode1is4 = true;  // Set to true to dis-able Binary TeX output

$max_file_time = 1;

$max_process_time = 120;

$max_num_processes = 30;

$timelimit_binary = '/usr/bin/timeout';

$maxima_binary = '/usr/bin/maxima';

$maxima_args = "";


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

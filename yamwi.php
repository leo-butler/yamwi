<?php

// COPYRIGHT NOTICE
// 
// Copyright 2009-2015 by Mario Rodriguez Riotorto <mario@edu.xunta.es>
// Copyright 2024 Leo Butler <leo.butler@umanitoba.ca>
// 
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License, version 2.
// 
// This program has NO WARRANTY, not even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// For details, see the LICENSE file.

// DESCRIPTION
//
// A simple php-web interface for Maxima.
// Basically, it creates a file with user input
// and calls Maxima in batch mode.




///////////////////
// USER SETTINGS //
///////////////////

require 'yamwi-conf.php';

///////////////////
// Sanity checks //
///////////////////

function check_maxima() {
    global $maxima_binary;
    if (! file_exists($maxima_binary) ) {
        echo '<pre class="fatal-error">Maxima binary not found. Halt.</pre>';
        exit();
    }
}

function check_ffmpeg() {
    global $ffmpeg_binary;
    if (! file_exists($ffmpeg_binary) ) {
        echo '<pre class="continuable-error">Ffmpeg binary not found: `draw_movie` disabled.</pre>';
        $ffmpeg_binary=0;
    }
}

function check_gnuplot() {
    global $gnuplot_binary, $gnuplot_args;
    if (! file_exists($gnuplot_binary) ) {
        echo '<pre class="continuable-error">Gnuplot binary not found: plotting is disabled.</pre>';
        $gnuplot_binary=0;
        $gnuplot_args="";
    }
}

/////////////
// Workers //
/////////////

function b64toa($b64) {
    $trans = array('=' => '-', '+' => '_', '/' => '~');
    return strtr($b64,$trans);
}
function atob64($str) {
    $trans = array('-' => '=', '_' => '+', '~' => '/');
    return strtr($str,$trans);
}
function atou($str) {return base64_decode(atob64($str));}

//////////////////////
// Global variables //
//////////////////////


$key = $_GET["c"] ?? "" ;
$nproc = $_GET["n"] ?? null;
$input = trim(($_POST["max"] ?? atou($_GET["max"] ?? "")) ?? "");
$mode = $_GET["mode"] ?? $mode;
if ($mode == 1 && $mode1is4 == true) { $mode = 4; }
$apache_user_name = shell_exec('whoami');
$yamwi_path = getcwd();
$dangerous_words =
   array(
   // Prevent LISP code from being executed
   ':lisp',':lisp-quiet','to_lisp','to-maxima',
   // Prevent access to LISP variables/functions from Maxima
   '?',
   // Prevent calls to a shell
   'system',
   // Prevent strings from being evaluated
   'eval_string',
   // Prevent writing to filesystem
   'compfile','compile','compile_file','translate','translate_file',
   'opena','openr','openw','write_data',
   // Filter IO commands (from section 13.2 of Maxima manual)
   'appendfile','batch','batchload',
   'closefile','file_output_append','filename_merge',
   'file_search','file_search_maxima','file_search_lisp',
   'file_search_demo','file_search_usage','file_search_tests',
   'file_type','file_type_lisp','file_type_maxima',
   'load_pathname','loadfile',
   'loadprint','pathname_directory','pathname_name',
   'pathname_type','printfile','save',
   'stringout','with_stdout','writefile',
   // Prevent snooping
   'run_testsuite','bug_report','build_info','room','status','demo',
   'filename_merge','file_search','file_type','directory',
   'pathname_directory','pathname_name','pathname_type',
   // Prevent package loading
   'batch','batchload','loadfile','setup_autoload',
   // Prevent reading files
   'read_matrix','read_lisp_array','read_maxima_array','read_hashed_array',
   'read_nested_list','read_list','entermatrix',
   // Prevent misc. graphics operations
   'openplot_curves','xgraph_curves','plot2d_ps','psdraw_curve','pscom',
   // Prevent string/symbol creation
   'concat','sconcat','printf','string','readbyte','readchar','readline','writebyte',
   'make_string_input_stream','make_string_output_stream','get_output_stream_string',
   // Prevent access to functions/variables in yamwi.mac
   'file_search_maxima','file_search_lisp','%num_sentence%','%num_grafico%','mwdrawxd','Draw2d','Draw3d','Draw','mwplotxd','Plot2d','Plot3d','Scatterplot','Histogram','Barsplot','Piechart','Boxplot','Starplot','Drawdf','translate_into_tex','translate_into_print','yamwi_display','oned_display','twod_display','mathml','set_alt_display','set_prompt','reset_displays',
   'maxima_tempdir', 'maxima_userdir', '%num_proceso%', '%codigo_usuario%', '%dir_sources%', '%movie_muxer%', '%movie_is_embedded%', '%ffmpeg_binary%', '%base64_cmd%', '%output_mode%', '%gcl%',
   // Prevent access to gnuplot-related variables/settings
   'gnuplot_',
   // not available
   'plotdf', 'showtime', 'julia', 'mandelbrot'
   );




////////////////
// Debug info //
////////////////


$show_info = ($key == $magic_key);
if ($show_info)
  echo '<u>Input: </u><pre>'.$input.'</pre><br/>'.
       '<u>Maximum time for files in tmp folder (min)</u>:<pre>'.$max_file_time.'</pre><br/>'.
       '<u>Maximum time for running a process (sec)</u>:<pre>'.$max_process_time.'</pre><br/>'.
       '<u>Maximum number of processes at a time</u>: <pre>'.$max_num_processes .'</pre><br/>';





/////////////////////////
// Auxiliary functions //
/////////////////////////



// create a key and store it in $key
function create_key() {
  global $key;
  $caracteres = "abcdefghijklmnopqrstuvwxyz0123456789";
  $i = 0;
  $cha = '' ;
  while ($i <= 20) {
    $num = rand() % 33;
    $tmp = substr($caracteres, $num, 1);
    $cha = $cha . $tmp;
    $i++;}
  $key = $cha;
  return $cha; }



// removes old files
function remove_old_files () {
  global $max_file_time, $yamwi_path;
  shell_exec('find ' . $yamwi_path . '/tmp/* -amin +"' .
              $max_file_time . '" -type f -exec rm -f {} \;'); }



//////////////////////
// Security section //
//////////////////////



// check for dangerous code
function dangerous ($code) {
  global $dangerous_words;
  $alert = false;
  foreach ($dangerous_words as $word) {
    if (! str_contains($code, $word) === false)  {
      $alert = $word;
      break; }}
  return $alert;}



// checks if the number of Maxima processes opened by Yamwi exceeds $max_num_processes
function too_many_processes() {
  global $max_num_processes, $apache_user_name, $show_info;
  $cmd = 'ps -eo time,pid,user,pcpu,pmem,args ' .
         ' | grep ' . trim($apache_user_name) .
         ' | grep maxima | sort -rn';
  $out = shell_exec($cmd);
  $num = ceil((count(explode("\n", $out))-2)/2);
  if ($show_info)
    echo '<u>Current Yamwi activity</u>: '.'<pre>'.$out.'</pre><br>'.
         '<u>Current number of Maxima processes</u>: <pre>'.$num .'</pre><br>';
  if ($num > $max_num_processes)
    return true;
  else
    return false;}



////////////////////
// Output section //
////////////////////


function write_form() {
    global $key, $nproc, $input, $submit_button, $clear_button, $mode;
  echo '<form id="maxform" method="post" action="'.
       $_SERVER["SCRIPT_NAME"] .'?c=' . $key . '&n=' . $nproc. '&mode=' . $mode . "&max=''\">\n".
       "<textarea id=\"max\" name=\"max\" rows=\"10\">\n".
       $input.
       "</textarea><br>\n".
       "<input type=\"button\" value=\"".
            $submit_button.
            "\" onClick=\"this.form.action=this.form.action.replace(/&max=[^&]*/,'&max='+utoa(this.form.max.value)); location.href=this.form.action;\">\n".
       "<input type=\"button\" value=\"".
            $clear_button.
            "\" onClick=\"this.form.max.value=''; return false\">\n".
       "<select name=\"modeselect\" id=\"modeselect\" class=\"modeselect\" onchange=\"this.form.action=this.form.action.replace(/&mode=[0-9]/,'&mode='+document.getElementById('modeselect').value); show_output(document.getElementById('modeselect').value); return true;\">\n".
       '   <option value="" disabled>Select Print Mode</option>'.
       '   <option value=0 '. ($mode == 0 ? "selected=\"selected\"" : "") . ">0 - Syntactic output</option>\n".            # ascii
       '   <option value=1 '. ($mode == 1 ? "selected=\"selected\"" : "") . ">1 - Ascii-Art output</option>\n".            # ascii-art
       '   <option value=2 '. ($mode == 2 ? "selected=\"selected\"" : "") . ">2 - Enhanced ASCII-Art output</option>\n".   # enhanced-ascii-art
       '   <option value=3 '. ($mode == 3 ? "selected=\"selected\"" : "") . ">3 - MathML</option>\n".                      # mathml
       '   <option value=4 '. ($mode == 4 ? "selected=\"selected\"" : "") . ">4 - Remote TeX + MathJax</option>\n".        # tex-mathjax
       "</select>\n".
       "<select name=\"inmodeselect\" id=\"inmodeselect\" class=\"modeselect\" onchange=\"show_input(document.getElementById('inmodeselect').value); return true;\">\n".
       '   <option value="" disabled>Select Input Mode</option>'.
       '   <option value=0>                    0 - Interpreted input</option>'.
       '   <option value=1 selected="selected">1 - Verbatim input</option>'.
       "</select>\n".
       "</form>\n".
       "<hr>\n\n" ; }


function write_results ($val) {
  echo $val;}

function gtlt ($str) {
  return str_replace(">", "&gt;", str_replace("<", "&lt;", $str));}

// prepare output
function prepare_output($out) {
  global $key, $nproc;
  // read and clean Maxima output
  $subout = re_process(trim($out));
  // write html code
  write_form();
  write_results($subout); }


// returns an alert message if something was wrong
function alert ($message) {
  write_results('<p class="error">' . $message . '</p>');}


function error_detected ($out) {
  global $message_prog_error;
  if (! strpos($out, "Maxima encountered a Lisp error:") === false ||
      ! strpos($out, "incorrect syntax:") === false ||
      ! strpos($out, "-- an error. To debug this try: debugmode(true);") === false)
      return $message_prog_error;
  else
      return false;}



function pre_process ($str) {
   $tmp = str_replace(array("\\\\","\\",""), array("","","\\\\\\\\"), $str);
   $tmp = str_replace(array("wxdraw3d", "draw3d"), "Draw3d", $tmp);
   $tmp = str_replace(array("wxdraw2d", "draw2d"), "Draw2d", $tmp);
   $tmp = str_replace(array("wxdraw", "draw"), "Draw", $tmp);
   $tmp = str_replace(array("wxplot3d", "plot3d"), "Plot3d", $tmp);
   $tmp = str_replace(array("wxplot2d", "plot2d"), "Plot2d", $tmp);
   $tmp = str_replace("scatterplot" , "Scatterplot", $tmp);
   $tmp = str_replace("histogram"   , "Histogram", $tmp);
   $tmp = str_replace("barsplot"    , "Barsplot", $tmp);
   $tmp = str_replace("piechart"    , "Piechart", $tmp);
   $tmp = str_replace("boxplot"     , "Boxplot", $tmp);
   $tmp = str_replace("starplot"    , "Starplot", $tmp);
   $tmp = str_replace("drawdf"      , "Drawdf", $tmp);
   return $tmp;}
function  re_process ($str) {
   // GCL ignores --very-quiet flag:
   $tmp = preg_replace('/read and interpret.+/','',$str);
   // GCL's si:run-process prints a funny message and we can't seem to re-direct it.
   $tmp = preg_replace('/\*+ Spawning process.+/','',$tmp);
   $tmp = str_replace("\\\\","\\", $tmp);
   $tmp = str_replace("Draw3d","draw3d",  $tmp);
   $tmp = str_replace("Draw2d","draw2d",  $tmp);
   $tmp = str_replace("Draw",  "draw",    $tmp);
   $tmp = str_replace("Plot3d","plot3d",  $tmp);
   $tmp = str_replace("Plot2d","plot2d",  $tmp);
   $tmp = str_replace("Scatterplot" , "scatterplot" , $tmp);
   $tmp = str_replace("Histogram"   , "histogram"   , $tmp);
   $tmp = str_replace("Barsplot"    , "barsplot"    , $tmp);
   $tmp = str_replace("Piechart"    , "piechart"    , $tmp);
   $tmp = str_replace("Boxplot"     , "boxplot"     , $tmp);
   $tmp = str_replace("Starplot"    , "starplot"    , $tmp);
   $tmp = str_replace("Drawdf"      , "drawdf"      , $tmp);
   return $tmp;}



// run Maxima and output results
function calculate () {
  global $key, $nproc, $input, $max_process_time, $message_time_process, $message_prog_error, $show_info,
      $mode, $yamwi_path, $timelimit_binary, $maxima_args, $maxima_binary, $gnuplot_binary, $gnuplot_args, $mode, $movie_muxer, $movie_is_embedded, $ffmpeg_binary, $base64_cmd;
  $nproc = $nproc + 1;
  check_maxima();
  check_ffmpeg();
  check_gnuplot();

  // build Maxima program
  // maxima_tempdir is hard-coded to be ./tmp
  // Set-up code is written to a different file, so snoopers cannot access it via _
  $val = '(maxima_tempdir: "'.$yamwi_path.'/tmp",' .
      'maxima_userdir: "'.$yamwi_path.'/.maxima",'.
      'gnuplot_command: "'.$gnuplot_binary.($gnuplot_args=="" ? "" : ' '.$gnuplot_args).'",'.
      '%codigo_usuario%: "'.$key.'",' .
      '%num_proceso%: "'.$nproc.'",' .
      '%dir_sources%: "'.$yamwi_path.'/packages",' .
      '%movie_muxer%: "'.$movie_muxer.'",'.
      '%movie_is_embedded%: if %gcl%=true then 0 else '.$movie_is_embedded.','.
      '%ffmpeg_binary%: "'.$ffmpeg_binary.'",'.
      '%base64_cmd%: "'.$base64_cmd.'",'.
      '%output_mode%:' . $mode . ')$' . "\n" .
      '(linenum:0,kill(labels),%%%)$';

  $yamwi_setup_mac = $yamwi_path.'/tmp/'.$key.'-setup.mac';
  $fich = fopen($yamwi_setup_mac, 'w');
  fwrite($fich, $val);
  fclose($fich);

  // create batch file
  $yamwi_mac = $yamwi_path.'/tmp/'.$key.'.mac';
  $fich = fopen($yamwi_mac, 'w');
  fwrite($fich, pre_process($input));
  fclose($fich);

  // call Maxima in batch mode
  $maxima_command = $maxima_binary . ' --very-quiet ' . $maxima_args . ' -p ' . $yamwi_path . '/yamwi.mac -p ' . $yamwi_path . '/yamwi.lisp' . ' -p ' . $yamwi_setup_mac . ' --batch-string='."'".'yamwi_batch("'.$yamwi_mac.'");'."'";
  if ($show_info) {echo '<u>Maxima command</u>: <pre>' . $maxima_command . '</pre><br/>';}
  // timelimit
  if (preg_match('/timelimit/',$timelimit_binary) == 1) {
      echo $timelimit_binary;
      $out = shell_exec($timelimit_binary . ' -t ' . $max_process_time . ' -T 5 ' . $maxima_command);
  } else {
  // timeout
      $out = shell_exec($timelimit_binary . ' --signal=TERM --kill-after=5 ' . $max_process_time . ' ' . $maxima_command);
  }

  if ($show_info){
    echo '<u>Complete Maxima input</u>: '.'<pre>'.$val.'</pre><br>';
    echo '<u>Complete Maxima output</u>: '.'<pre>'.str_replace('<','〈',str_replace('>','〉',$out)).'</pre><br>';}

  // Checks whether the last line returned by the shell call
  // contains the path to the Maxima script; if not, it
  // means that the process has been interrupted by timelimit.
  // Note: this check is only valid if linel is large enough to avoid splitting the string.
  $end_needle='<!--END-->';
  $end=strrpos(trim($out),$end_needle,0);
  $an_error = false;
  if (! $end === false) {
    $out = substr($out,0,$end);
    if ($show_info) {
        echo '<u>end:</u> '.$end.'<br/>';}
    $start_needle='<!--START-->';
    $start=strrpos($out,$start_needle);
    if ($show_info) {
        echo '<u>start:</u> '.$start.'<br/>';}
    if (! $start === false) {
        $out = substr($out,$start+strlen($start_needle));
        // write results
        $an_error = error_detected($out);
    }
  };
  if ($end === false || $start === false) {
      write_form();
      alert($message_prog_error);}
  else {
      if (! $an_error === false) {
          write_form();
          alert ($an_error);}
      else
          prepare_output($out);
  }
  // cleaning old files
  remove_old_files ();}



//////////////////
// Main program //
//////////////////


function start ($initial_code) {
  global $key, $nproc, $input, $message_dangerous, $message_too_many_processes;
  $danger = dangerous($input);
  if ($key == "") {
    $nproc = 0;
    $input = $initial_code;
    create_key();
    write_form(); }
  elseif ($danger) {
    write_form();
    alert($message_dangerous . $danger );}
  elseif (too_many_processes()) {
    write_form();
    alert($message_too_many_processes);}
  else
    calculate();}

?>

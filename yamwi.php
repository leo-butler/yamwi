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

//////////////////////
// Global variables //
//////////////////////


$key = $_GET["c"] ?? "" ;
$nproc = $_GET["n"] ?? null;
$input = trim(($_POST["max"] ?? base64_decode($_GET["max"] ?? "")) ?? "");
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
   'load','load_pathname','loadfile',
   'loadprint','pathname_directory','pathname_name',
   'pathname_type','printfile','save',
   'stringout','with_stdout','writefile',
   // Prevent snooping
   'run_testsuite','bug_report','build_info','room','status','demo',
   'filename_merge','file_search','file_type','directory',
   'pathname_directory','pathname_name','pathname_type',
   // Prevent package loading
   'batch','batchload','load','loadfile','setup_autoload',
   // Prevent reading files
   'read_matrix','read_lisp_array','read_maxima_array','read_hashed_array',
   'read_nested_list','read_list','entermatrix',
   // Prevent misc. graphics operations
   'openplot_curves','xgraph_curves','plot2d_ps','psdraw_curve','pscom',
   // Prevent string/symbol creation
   'concat','sconcat','printf','string','readbyte','readchar','readline','writebyte',
   'make_string_input_stream','make_string_output_stream','get_output_stream_string',
   // Prevent access to functions/variables in yamwi.mac
   'file_search_maxima','file_search_lisp','%num_sentence%','%num_grafico%','mwdrawxd','Draw2d','Draw3d','Draw','mwplotxd','Plot2d','Plot3d','Scatterplot','Histogram','Barsplot','Piechart','Boxplot','Starplot','Drawdf','translate_into_tex','translate_into_print',
   // not available
   'plotdf'
   );




////////////////
// Debug info //
////////////////


$show_info = false;
if ($show_info)
  echo '<u>Maximum time for files in tmp folder (min)</u>:<pre>'.$max_file_time.'</pre><br>'.
       '<u>Maximum time for running a process (sec)</u>:<pre>'.$max_process_time.'</pre><br>'.
       '<u>Maximum number of processes at a time</u>: <pre>'.$max_num_processes .'</pre><br>';





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



// separates individual sentences from Maxima script
function input_sentences ($val) {
  $anchor = 0;
  $sentence_counter = 0;
  $comment_level = 0;
  for($i = 0 ; $i < strlen($val) ; $i++) {
    if ($val[$i] == "/" && $val[$i+1] == "*") 
      $comment_level = $comment_level + 1;
    if ($val[$i] == "*" && $val[$i+1] == "/") 
      $comment_level = $comment_level - 1;
    if ($comment_level == 0 &&
        ($val[$i] == ";" || $val[$i] == "$")) {
      $sentences[$sentence_counter]= substr($val, $anchor, $i - $anchor + 1);
      $sentence_counter = $sentence_counter + 1;
      $anchor = $i+1;} }
  return $sentences; }



// Builds Maxima list of sentences
function list_of_sentences ($sentences) {
  global $mode;
  $sentence_counter = count($sentences);
  $lista = "";
  for($i = 1 ; $i < $sentence_counter ; $i++){
    $lista = $lista . 
             "\"" .
             str_replace("\"", "\\\"" , $sentences[$i]) .
             "\"";
  if ($i < $sentence_counter-1)
    $lista = $lista.",\n";}
  if ($mode == 1 || $mode == 4)
    $lista = $sentences[0]."\ntranslate_into_tex([".$lista."])$";
  else
    $lista = $sentences[0]."\ntranslate_into_print([".$lista."])$";
  return $lista;}



// returns the necessary code to add all
// the requested graphics when working in ASCII mode.
function graphics() {
   global $key, $nproc;
  $result = "";
  $file = 'tmp/' . $key . '.gr.' . $nproc . '.0.txt';
  if (file_exists($file)) {
    $fh = fopen($file, 'r');
    $theData = trim(fread($fh, filesize($file)));
    fclose($fh); 
    $out = explode("\n", $theData);
    foreach ($out as $file_name)
      $result = $result .
                '<img src=' . $file_name . ' alt="gr"><br>'; }
  return $result; }



function write_form() {
    global $key, $nproc, $input, $submit_button, $clear_button, $mode;
  echo '<form id="maxform" method="post" action="'.
       $_SERVER["SCRIPT_NAME"] .'?c=' . $key . '&n=' . $nproc. '&mode=' . $mode . "&max=" . base64_encode($input) . "\">\n".
       "<textarea name=\"max\" rows=\"10\">\n".
       $input.
       "</textarea><br>\n".
       "<input type=\"button\" value=\"".
            $submit_button.
            "\" onClick=\"this.form.action=this.form.action.replace(/&max=[^&]*/,'&max='+btoa(this.form.max.value)); location.href=this.form.action;\">\n".
       "<input type=\"button\" value=\"".
            $clear_button.
            "\" onClick=\"this.form.max.value=''; return false\">\n".
       "<select name=\"modeselect\" id=\"modeselect\" class=\"modeselect\" onchange=\"this.form.action=this.form.action.replace(/&mode=[0-9]/,'&mode='+this.form.getElementsByTagName('select')[0].value); this.form.getElementsByTagName('input')[0].click(); return true;\">\n".
       '   <option value="" disabled>Select Print Mode and Submit</option>'.
       '   <option value=0 '. ($mode == 0 ? "selected=\"selected\"" : "") . ">0 - ASCII-Art output</option>\n".
       '   <option value=1 '. ($mode == 1 ? "selected=\"selected\"" : "") . ">1 - Binary TeX output</option>\n".
       '   <option value=2 '. ($mode == 2 ? "selected=\"selected\"" : "") . ">2 - Enhanced ASCII-Art output</option>\n".
       '   <option value=3 '. ($mode == 3 ? "selected=\"selected\"" : "") . ">3 - Syntactic output</option>\n".
       '   <option value=4 '. ($mode == 4 ? "selected=\"selected\"" : "") . ">4 - Remote TeX + MathJax</option>\n".
       '   <option value=4 '. ($mode == 5 ? "selected=\"selected\"" : "") . ">5 - MathML (not implemented)</option>\n".
       "</select>\n".
       "</form>\n".
       "<hr>\n\n" ; }



function write_results ($val) {
  echo $val;}



function gtlt ($str) {
  return str_replace(">", "&gt;", str_replace("<", "&lt;", $str));}



function prepare_ascii_output($out) {
  write_form();
  write_results('<pre>' .
                gtlt(substr($out, strpos($out, "(%i3)"))) .
                '</pre>' .
                graphics());}



function prepare_enhanced_ascii_output($out, $sentences) {
  global $key, $nproc;
  $out_counter = 0;

  // read and clean Maxima output
  $subout = trim($out);
  $subout = substr($subout,31+strpos($subout,"start_maxima_output_print_code:"));

  // scan Maxima output
  while (strlen($subout) > 0) {
    $text_code_ini = strpos($subout,"%%%");
    $print_code[$out_counter] = substr($subout,0,$text_code_ini);
    if ($print_code[$out_counter] != '')
      $print_code[$out_counter] = '<pre class="print">' . gtlt($print_code[$out_counter]) . '</pre>';
    $image_code[$out_counter] = search_images($out_counter);
    $text_code_end = strpos(substr($subout,$text_code_ini+3),"%%%");
    $text_code[$out_counter] = trim(substr($subout, $text_code_ini, $text_code_end+6), "%");
    $subout = substr($subout, $text_code_ini+$text_code_end+6);
    $out_counter = $out_counter + 1; }

  // write html code
  write_form();
  $result = "<table>\n";
  for($i = 1 ; $i <= $out_counter ; $i++) {
    $this_result1 = '';  // the output label
    $this_result2 = '';  // the mathematical result
    if (substr($sentences[$i], -1) === ";") {
      $this_result1 = '(%o' . $i . ')';
      $this_result2 = '<pre class="output">' . $text_code[$i-1] . '</pre>'; }
    $result = $result .
              '<tr>' .
              '<td><pre class="input">' . '(%i' . $i . ')' . "</pre></td>\n" .
              '<td><pre class="inputcode">' . trim($sentences[$i]) . "</pre>\n".
              $print_code[$i-1] .
              "</td>\n" .
              "</tr>\n" .
              '<tr>' .
              '<td><pre class="output">' . $this_result1 . "</pre></td>\n" .
              '<td>' . $image_code[$i-1] . $this_result2 . "<br></td>\n" .
              "</tr>\n";}
  $result = $result . "</table>\n\n";
  write_results($result); }



// search for images returned by sentence number $sn
// and write the corresponding html code
function search_images ($sn) {
  global $key, $nproc;
  $result = "";
  $sen = $sn + 1;
  $file = 'tmp/' . $key . '.gr.' . $nproc .  '.' . $sen . '.txt';
  if (file_exists($file)) {
    $fh = fopen($file, 'r');
    $theData = trim(fread($fh, filesize($file)));
    fclose($fh); 
    $out = explode("\n", $theData);
    foreach ($out as $file_name) {}
      while (! file_exists($file_name)) {};
      $result = $result . '<img src = "' . $file_name .'" alt="gr"><br>';}
  return $result; }



function latex_template ($tex) {
  return "\documentclass{article}\n" .
         "\usepackage{amsmath,amssymb}\n".
         "\pagestyle{empty}\n" .
         "\begin{document}\n" .
         $tex .
         "\n\\end{document}\n" ;}



function prepare_tex_output($out, $sentences) {
  global $key, $nproc, $yamwi_path, $mode;
  $out_counter = 0;

  // read and clean Maxima output
  $subout = trim($out);
  $subout = substr($subout,31+strpos($subout,"start_maxima_output_tex_code:"));
  $subout=str_replace("\begin{verbatim}", "$$", str_replace("\end{verbatim}", "$$", $subout));

  // scan Maxima output
  while (strlen($subout) > 0) {
    $tex_code_ini = strpos($subout,"$$");
    $print_code[$out_counter] = substr($subout,0,$tex_code_ini);
    if ($print_code[$out_counter] != '')
      $print_code[$out_counter] = '<pre class="print">' .
                                  gtlt($print_code[$out_counter]) .
                                  '</pre>';
    $image_code[$out_counter] = search_images($out_counter);
    $tex_code_end = strpos(substr($subout,$tex_code_ini+2),"$$");
    $tex_code[$out_counter] = substr($subout, $tex_code_ini, $tex_code_end+4);
    $subout = substr($subout, $tex_code_ini+$tex_code_end+4);
    $out_counter = $out_counter + 1; }

  if ($mode == 1) {
    // save LaTex files
    for($i = 1 ; $i <= $out_counter ; $i++){
      if (substr($sentences[$i], -1) === ";") {
        // write latex file
        $fich = fopen($yamwi_path . '/tmp/' . $key . '-' . $nproc . '-' . $i . '.tex', 'w');
        fwrite($fich, latex_template($tex_code[$i-1]));
        fclose($fich);
        // compile latex source
        shell_exec(
          'cd ' . $yamwi_path . '/tmp/' . ';' .
          'texi2dvi ' . $key . '-' . $nproc . '-' . $i . '.tex ;' .
          'dvips -E ' . $key . '-' . $nproc . '-' . $i . '.dvi ;' .
          'convert -density 150x150 '. $key . '-' . $nproc . '-' . $i .'.ps '.$key.'.'.$nproc.'.'.$i.'.png'); } }}

  // write html code
  if ($mode == 4) {
    $result = '<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>' . "\n\n";}
  else {
    $result = '';}
  write_form();
  $result = $result . "<table>\n";
  for($i = 1 ; $i <= $out_counter ; $i++) {
    $this_result1 = '';  // the output label
    $this_result2 = '';  // the mathematical result
    if (substr($sentences[$i], -1) === ";") {
      $this_result1 = '(%o' . $i . ')';
      if ($mode == 1) {
        $this_result2 = '<img src='.'tmp/'.$key.'.'.$nproc.'.'.$i.'.png'.' alt="eq">';}
      else {
        $this_result2 = '\(' . trim($tex_code[$i-1],"$") . '\)';} }
    $result = $result .
              '<tr>' .
              '<td><br><pre class="input">' . '(%i' . $i . ')' . "</pre></td>\n" .
              '<td><br><pre class="inputcode">' . trim($sentences[$i]) . "</pre>\n" .
              $print_code[$i-1] .
              "</td>\n" .
              "</tr>\n" .
              '<tr>' .
              '<td class="output">' . $this_result1 . "</td>\n" .
              '<td class="output">' . $image_code[$i-1] . $this_result2 . "</td>\n" .
              "</tr>\n"; }
  $result = $result . "</table>\n\n";
  write_results($result); }



// returns an alert message if something was wrong
function alert ($message) {
  write_results('<p class="error">' . $message . '</p>');}



function error_detected ($out) {
  global $message_prog_error;
  $yamwi1a_pos = strpos($out, "yamwi1a");
  if (! $yamwi1a_pos === false)
    return "Not enough information on some of: " . 
           substr($out,
                  $yamwi1a_pos + 7,
                  strpos($out, "yamwi1b") - $yamwi1a_pos - 7) .
           ".<br>You may try 'assume'." ;
  elseif (! strpos($out, "Maxima encountered a Lisp error:") === false ||
          ! strpos($out, "incorrect syntax:") === false ||
          ! strpos($out, "-- an error. To debug this try: debugmode(true);") === false)
    return $message_prog_error;
  else
    return false;}



function pre_process ($str) {
   $tmp = str_replace("\\", "" , $str);
   $tmp = str_replace(array("wxdraw3d", "draw3d"), "Draw3d", $tmp);
   $tmp = str_replace(array("wxdraw2d", "draw2d"), "Draw2d", $tmp);
   $tmp = str_replace(array("wxdraw", "draw"), "Draw", $tmp);
   $tmp = str_replace(array("wxplot3d", "plot3d"), "Plot3d", $tmp);
   $tmp = str_replace(array("wxplot2d", "plot2d"), "Plot2d", $tmp);
   return $tmp;}



// run Maxima and output results
function calculate () {
  global $key, $nproc, $input, $max_process_time, $message_time_process, $show_info,
      $mode, $yamwi_path, $timelimit_binary, $maxima_args, $maxima_binary;
  $nproc = $nproc + 1;
  $display2d = "";
  if ($mode == 3) $display2d = "display2d: false,";

  // build Maxima program
  $val = '(maxima_tempdir: "'.$yamwi_path.'/tmp",' .
         '%codigo_usuario%: "'.$key.'",' .
         '%num_proceso%: "'.$nproc.'",' .
         '%dir_sources%: "'.$yamwi_path.'/packages",' .
         'load("'.$yamwi_path.'/yamwi.mac"),' .
         'load("'.$yamwi_path.'/yamwi.lisp"),' .
         $display2d .
         "\"%%%\")\$\n" . 
         $input;
  $val = pre_process ($val);

  // in TeX or enhanced ASCII mode, isolate sentences.
  if ($mode == 1 || $mode == 2 || $mode == 3 || $mode == 4) {
    // 1. make array of input sentences
    $sentences = input_sentences($val);
    // 2. build the Maxima list with sentences as strings
    $val = list_of_sentences($sentences); }

  // create batch file
  $fich = fopen($yamwi_path.'/tmp/'.$key.'.mac', 'w');
  fwrite($fich, $val);
  fclose($fich);

  // call Maxima in batch mode
  // timelimit
  if (preg_match('/timelimit/',$timelimit_binary) == 1) {
      echo $timelimit_binary;
    $out = shell_exec($timelimit_binary . ' -t ' .
                      $max_process_time .
    		      ' -T 5 ' . $maxima_binary . ' ' . $maxima_args . ' -b "'.$yamwi_path.'/tmp/'.$key.'.mac"');
		      } else {
  // timeout
    $out = shell_exec($timelimit_binary . ' --signal=TERM --kill-after=5 ' .
                      $max_process_time . ' ' .
                      $maxima_binary . ' ' . $maxima_args . ' -b "'.$yamwi_path.'/tmp/'.$key.'.mac"');}

  if ($show_info){
    echo '<u>Complete Maxima input</u>: '.'<pre>'.$val.'</pre><br>';
    echo '<u>Complete Maxima output</u>: '.'<pre>'.$out.'</pre><br>';}

  // Checks wether the last line returned by the shell call
  // contains the path to the Maxima script; if not, it
  // means that the process has been interrupted by timelimit.
  if (str_contains(substr($out,strrpos(trim($out), "\n", -1)), $yamwi_path.'/tmp/'.$key.'.mac')) {
    $out = substr($out,strpos($out, "%%%") + 4);
    $out = rtrim(str_replace($yamwi_path.'/tmp/'.$key.'.mac','', $out));
    $out = substr($out,0, strlen($out) - strlen(strrchr($out,"%")) - 1);
    $input = str_replace("\\", "" , $input);

    // write results
    $an_error = error_detected($out);
    if (! $an_error === false) {
      write_form();
      alert ($an_error);}
    elseif ($mode == 0)  // ASCII mode
      prepare_ascii_output($out);
    elseif ($mode == 1 || $mode == 4) // TeX or MathJax mode
      prepare_tex_output($out, $sentences);
    else  // Enhanced ASCII and syntactic modes
      prepare_enhanced_ascii_output($out, $sentences);

    // cleaning old files
    remove_old_files ();}

  else {
    write_form();
    alert($message_time_process); }}



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

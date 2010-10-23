<?php

// COPYRIGHT NOTICE
// 
// Copyright 2009-2010 by Mario Rodriguez Riotorto
//    http://yamwi.sourceforge.net
//    mario@edu.xunta.es
// 
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License, version 2.
// 
// This program has NO WARRANTY, not even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

// A simple php-web interface for Maxima.
// Basically, it creates a file with user input
// and calls Maxima in batch mode.




///////////////////
// USER SETTINGS //
///////////////////



$mode = 1; // 0 -> ASCII-art output, 1-> TeX output

$max_file_time = 1;

$max_process_time = 120;

$max_num_processes = 30; 

$message_dangerous = "Yamwi detected dangerous code !!";

$message_time_process = "Requested process aborted. It exceeded maximum execution time.";

$message_too_many_processes = "Too many users. Please, try later.";

$message_prog_error = "Programming error detected. Check your input.";

$dangerous_words =
   array(':lisp',':lisp-quiet','to_lisp','to-maxima','system','eval_string',
         'compfile','compile','translate','translate_file','compile_file',
         'run_testsuite','bug_report','build_info','demo','appendfile',
         'batch','batch','closefile','filename_merge','file_search',
         'file_type','loadfile','save','stringout','with_stdout','writefile',
         'room','status','setup_autoload','opena','openr','openw','read_matrix',
         'read_lisp_array','read_maxima_array','read_hashed_array',
         'read_nested_list','read_list','write_data','entermatrix',
         'openplot_curves','xgraph_curves','plot2d_ps','psdraw_curve',
         'pscom','plot2d','plot3d','concat','sconcat','?');

$initial_code = 
   "expand((x-2)^3*(x+1/3)^2);\n\n".
   "solve(x^2-x+2=0);\n\n".
   "invert(matrix([2,3,1], [a,0,0], [1,4,8]));\n\n".
   "integrate(x * sin(x), x);\n\n".
   "Draw2d(\n".
   "    color = red,\n".
   "    explicit(exp(x),x,-1,3),\n".
   "    color = blue,\n".
   "    line_width = 2,\n".
   "    parametric(2*cos(a),a^2,a,0,2*%pi));\n\n".
   "Draw3d(\n".
   "    enhanced3d=true,\n".
   "    explicit(u^2+v^2,u,0,2,v,-2,2));\n" ;










//////////////////////
// Global variables //
//////////////////////



$key = $_GET["c"];
$nproc = $_GET["n"];
$input = trim($_POST["max"]);
$apache_user_name = shell_exec('whoami');
$maxima_path = shell_exec('which maxima');
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
  global $max_file_time;
  $dir = getcwd();
  shell_exec('find ' . $dir . '/tmp/* -amin +"' .
              $max_file_time . '" -type f -exec rm -f {} \;'); }



//////////////////////
// Security section //
//////////////////////



// check for dangeorus code
function dangeorus ($code) {
  global $dangerous_words;
  $alert = false;
  foreach ($dangerous_words as $word) {
    if (! strstr($code, $word) === false)  {
      $alert = true;
      break; }}
  return $alert;}



// returns the number of Maxima processes
// opened from Yamwi
function too_many_processes() {
  global $max_num_processes, $apache_user_name, $show_info, $maxima_path;
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



// Construyo la lista maxima de sentencias
function list_of_sentences ($sentences) {
  $sentence_counter = count($sentences);
  $lista = "";
  for($i = 1 ; $i < $sentence_counter ; $i++){
    $lista = $lista . 
             "\"" .
             str_replace("\"", "\\\"" , $sentences[$i]) .
             "\"";
  if ($i < $sentence_counter-1)
    $lista = $lista.",\n";}
  $lista = $sentences[0]."\ntranslate_into_tex([".$lista."])$";
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
                '<tr><td><img src=' .
                $file_name .
                '></td></tr>'; }
  return $result; }



function write_form() {
  global $key, $nproc, $input;
  echo "<center>\n".
       "  <form method=\"post\" action=\"index.php?c=".$key."&n=".$nproc."\">\n".
       "  <table>\n".
       "    <tr>\n".
       "      <td>\n".
       "        <textarea name=\"max\" cols=\"60\" rows=\"20\">\n".
                $input.
       "        </textarea>\n".
       "      </td>\n".
       "    </tr>\n".
       "  </table>\n".
       "  <input type=\"submit\" value=\"Clic\">\n".
       "  </form>\n".
       "  <hr width = \"50%\">\n".
       "</center>\n\n"; }



function write_results ($val) {
  echo "<center>\n".
       "  <table>\n".
       "    <tr>\n".
       "      <td>\n".
                $val .
       "      </td>\n".
       "    </tr>\n".
       "  </table>\n".
       "</center>\n\n";}



function prepare_ascii_output($out) {
  global $nproc;
  $result = $out . graphics();
  write_form();
  write_results('<pre>' . $result . '</pre>');}



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
      $result = $result . '<center><img src = "' . $file_name .'"></center><br>';}
  return $result; }



function latex_template ($tex) {
  return "\documentclass{article}\n" .
         "\pagestyle{empty}\n" .
         "\begin{document}\n" .
         $tex .
         "\n\end{document}\n" ;}



function prepare_tex_output($out, $sentences) {
  global $key, $nproc;
  $out_counter = 0;
  $dir = getcwd();

  // read and clean Maxima output
  $subout = trim($out);
  $subout = substr($subout,31+strpos($subout,"start_maxima_output_tex_code:"));

  // scan Maxima output
  while (strlen($subout) > 0) {
    $tex_code_ini = strpos($subout,"$$");
    $print_code[$out_counter] = substr($subout,0,$tex_code_ini);
    $image_code[$out_counter] = search_images($out_counter);
    $tex_code_end = strpos(substr($subout,$tex_code_ini+2),"$$");
    $tex_code[$out_counter] = substr($subout, $tex_code_ini, $tex_code_end+4);
    $subout = substr($subout, $tex_code_ini+$tex_code_end+4);
    $out_counter = $out_counter + 1; }

  // save LaTex files
  for($i = 1 ; $i <= $out_counter ; $i++){
    if (substr($sentences[$i], -1) === ";") {
      // write latex file
      $fich = fopen($dir . '/tmp/' . $key . '.tex', 'w');
      fwrite($fich, latex_template($tex_code[$i-1]));
      fclose($fich);
      // compile latex source
      shell_exec(
        'cd ' . $dir . '/tmp/' . ';' .
        'texi2dvi ' . $key . '.tex ;' . 
        'dvips -E ' . $key . '.dvi ;' . 
        'convert -density 150x150 '. $key .'.ps '.$key.'.'.$nproc.'.'.$i.'.png'); } }

  // write html code
  write_form();
  $result = "<table>";
  for($i = 1 ; $i <= $out_counter ; $i++) {
    $this_result1 = '';  // the output label
    $this_result2 = '';  // the mathematical result
    if (substr($sentences[$i], -1) === ";") {
      $this_result1 = '(%o' . $i . ')';
      $this_result2 = '<img src='.'tmp/'.$key.'.'.$nproc.'.'.$i.'.png'.'>'; }
    $result = $result .
              '<tr>' .
              '<td valign="top"><br>' . '(%i' . $i . ')' . '</td>' .
              '<td align="left"><font size="+2"><b><pre>' . 
              trim($sentences[$i]) .
              '</pre></b></font>' .
              '<pre>' . $print_code[$i-1] . '</pre>' .
              $image_code[$i-1] .
              '</td>' .
              '</tr>' .
              '<tr>' .
              '<td>' . $this_result1 . '</td>' .
              '<td align="center">' . $this_result2 . '</td>' .
              '</tr>'; }
  $result = $result . "</table>";
  write_results($result); }



// returns an alert message if something was wrong
function alert ($message) {
  write_results('<font size="+2" color="red">'.
                $message.
                '</font>');}



function error_detected ($out) {
  if (! strpos($out, "Maxima encountered a Lisp error:") === false ||
      ! strpos($out, "incorrect syntax:") === false ||
      ! strpos($out, "-- an error. To debug this try: debugmode(true);") === false)
    return true;
  else
    return false;}



// run Maxima and output results
function calculate () {
  global $key, $nproc, $input, $max_process_time, $message_time_process, $show_info, $message_prog_error, $mode;
  $nproc = $nproc + 1;
  $dir = getcwd();

  // build Maxima program
  $val = '(maxima_tempdir: "'.$dir.'/tmp",' .
         '%codigo_usuario%: "'.$key.'",' .
         '%num_proceso%: "'.$nproc.'",' .
         '%dir_sources%: "'.$dir.'/packages",' .
         'load("'.$dir.'/yamwi.mac"),' .
         "\"%%%\")\$\n" . 
         $input;
  $val = str_replace("\\", "" , $val);

  // in TeX mode, isolate sentences.
  if ($mode == 1) {
    // 1. make array of input sentences
    $sentences = input_sentences($val);
    // 2. build the Maxima list with sentences as strings
    $val = list_of_sentences($sentences); }

  // create batch file
  $fich = fopen($dir.'/tmp/'.$key.'.mac', 'w');
  fwrite($fich, $val);
  fclose($fich);

  // call Maxima in batch mode
  $out = shell_exec('timelimit -t '. 
                    $max_process_time .
                    ' -T 5 maxima -b "'.$dir.'/tmp/'.$key.'.mac"');
  if ($show_info){
    echo '<u>Complete Maxima input</u>: '.'<pre>'.$val.'</pre><br>';
    echo '<u>Complete Maxima output</u>: '.'<pre>'.$out.'</pre><br>';}
  
  // if process did not exceed maximum execution time
  // generate html output.
  if (strstr($out, 'Exiting on signal') === false) {
    $out = substr($out,strpos($out, "%%%") + 4);
    $out = rtrim(str_replace($dir.'/tmp/'.$key.'.mac','', $out));
    $out = substr($out,0, strlen($out) - strlen(strrchr($out,"%")) - 1);
    $input = str_replace("\\", "" , $input);

    // write results
    if (error_detected($out)) {
      write_form();
      alert ($message_prog_error);}
    elseif ($mode == 0)  // ASCII mode
      prepare_ascii_output($out);
    else // TeX mode
      prepare_tex_output($out, $sentences);

    // cleaning old files
    remove_old_files ();}

  else {
    write_form();
    alert($message_time_process); }}



//////////////////
// Main program //
//////////////////



if ($key == "") {
    $nproc = 0;
    $input = $initial_code;
    create_key();
    write_form(); } 
elseif (dangeorus($input)) {
  write_form();
  alert($message_dangerous);}
elseif (too_many_processes()) {
  write_form();
  alert($message_too_many_processes);}
else
  calculate();

?>

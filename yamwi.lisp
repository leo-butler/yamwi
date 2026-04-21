;; Make a string-stream for *ERROR-OUTPUT*, so that we can inspect it.
(setf *error-output* (make-string-output-stream))

;; RETRIEVE is the function used to ask questions of the user.
;; We redefine it here to return print the question and then throw to the tag macsyma-quit.
;; This halts further batch-ing of the script, but allows previously computed results to be printed
;; along with the question.
(defvar *retrieve-fun* (symbol-function 'retrieve))
(defun retrieve (form &rest optional)
  (declare (special $standard_output $original_standard_output))
  (let* (($linel most-positive-fixnum)
	 (prefix   "<tr class='retrieve'><td></td><td><span class='retrieve'>Maxima asked: <u>")
	 (suffix   "</u></span></td></tr>")
	 (error-prefix "<tr class='retrieve'><td></td><td><span class='retrieve'>")
	 (error-suffix "</span></td></tr>")
	 (epilogue "<tr class='retrieve'><td></td><td><span class='retrieve'>Questions can be answered by using <a href='https://maxima.sourceforge.io/docs/manual/Maxima_0027s-Database.html#index-assume'>assume</a> or typing `y;', `n;', `p;', `z;', `nz;' as appropriate.</span></td></tr>")
	 (result (catch 'macsyma-quit
		   (apply *retrieve-fun* `(,form ,@optional)))))
    (cond ((eq result 'maxima-error)
	   (mformat $original_standard_output "~M" `((mtext) ,prefix ,form ,suffix ,error-prefix ,(get-output-stream-string $standard_output) ,error-suffix ,epilogue))
	   (throw 'macsyma-quit 'maxima-error))
	  (t
	   result))))

;; to avoid problems with Internet browsers when reading strict ordering symbols
;; in inequalities (<, >) in LaTeX mode (mainly for MathJax), we need them
;; to be translated into \lt and \gt, respectively.
(defprop mlessp ("\\lt ") texsym)
(defprop mgreaterp ("\\gt ") texsym)
(defprop mlessp (#\  #\& #\l #\t #\; #\ ) dissym)
(defprop mgreaterp (#\  #\& #\g #\t #\; #\ ) dissym)

;; over-write parse-tyi (src/nparse.lisp)
(defun parse-tyi0 ()
  (let ((tem  *parse-tyi*))
    (cond ((null tem)
	   (tyi-parse-int *parse-stream* *parse-stream-eof*))
	  ((atom tem)
	   (setq *parse-tyi* nil)
	   tem)
	  (t ;;consp
	   (setq *parse-tyi* (cdr tem))
	   (car tem)))))
(defvar *input-char-stream* '())
(defvar *collect-input* t "If T, then PARSE-TYI collects input; otherwise, not.")
(defun parse-tyi ()
  (let ((x (parse-tyi0)))
    (and *collect-input* x (push x *input-char-stream*))
    x))
(defun $literal_input (x)
  (declare (special *input-char-stream*))
  (let ((c-tag (if (atom x) x (cadr x))))
    ;;(format t "~%~%c-tag = ~a~%input-char-stream = ~{~a~}~%" c-tag (reverse *input-char-stream*))
    (setf (get 'vinput c-tag) (format nil "~{~a~}" (reverse *input-char-stream*))
	  *input-char-stream*  '())
    (get 'vinput c-tag)))

;; We need to turn off the collection of input characters inside of BATCHLOAD-STREAM
;; Thanks to Jinsong Zhao, https://sourceforge.net/p/maxima/mailman/message/59292578/
(defvar *batchload-stream-fun* (symbol-function 'batchload-stream))
(defun batchload-stream (in-stream &key autoloading-p)
  (declare (special *collect-input* *batchload-stream-fun*))
  (let (*collect-input*)
    (funcall *batchload-stream-fun* in-stream :autoloading-p autoloading-p)))
;; $LOAD is a DEFMFUN, which means that LOAD-IMPL does all the work
(defvar *load-impl-fun* (symbol-function 'load-impl))
(defun load-impl (filename)
  (let ((*trusted-calculation* t))
    (declare (special *trusted-calculation*))
    (funcall *load-impl-fun* filename)))

;; patches to lurkmathml
;; `mfenced' is an unsupported mathml element
;; It is recommended to use `mrow' + `mo'
;; https://developer.mozilla.org/en-US/docs/Web/MathML/Reference/Element/mfenced
(defprop mlist (("<mrow><mo>[</mo>")"<mo>]</mo></mrow> ") mathmlsym)
(defprop mabs (("<mo>|</mo>")"<mo>|</mo> ") mathmlsym)
(defprop mprogn (("<mo>(</mo>") "<mo>)</mo> ") mathmlsym)

(defun mathml1 (mexplabel)
  (prog ((mexp (and mexplabel (meval mexplabel)))
	 (texport *standard-output*)
	 $gcprint) ;; $gcprint = nil turns gc messages off
     (cond ((null mexplabel)
	    (displa " No eqn given to MathML")
	    (return nil)))
     ;; display the expression for MathML now:
     (myprinc "<math xmlns=\"http://www.w3.org/1998/Math/MathML\"> " texport)
     (mapc #'(lambda (x) (myprinc x texport))
	   ;;initially the left and right contexts are
	   ;; empty lists, and there are implicit parens
	   ;; around the whole expression
	   (mathml mexp nil nil 'mparen 'mparen))
     (format texport "</math>")
     (fresh-line)
     (return mexplabel)))

;; asdf
#-gcl(require 'asdf      #+clisp #P"/usr/lib/clisp-2.49.95+/asdf/asdf.lisp")
;; cl-base64
;; (require 'cl-base64 #+clisp #p"/usr/share/common-lisp/source/cl-base64/cl-base64.asd")
;; (asdf:oos 'asdf:compile-op '#:cl-base64)
;; (asdf:oos 'asdf:load-op    '#:cl-base64)

;; over-write $system
;; we assume args is a single string

(defvar *system-fun* (symbol-function 'system-impl))

#+gcl(progn
       (defvar $%gcl% t)
       (defun gcl-run-program (args &key output error-output)
       (declare (ignore input output))
       ;;(format t "~%~{~a ~} output=~a error-output=~a" args output error-output)
       (let* ((*standard-output* (make-string-output-stream))
	      (*error-output*    (make-string-output-stream))
	      (exit-code         1)
	      (output (ignore-errors (si:run-process (car args) (cdr args)))))
	 (when (not (null output))
	   ;;(format t "~%<!--start-->~%")
	   (setq exit-code 0)
	   (loop for c = (read-line output nil)
		 while c do
		   (format t "~%~a" c)))
	 (values (get-output-stream-string *standard-output*)
		 (get-output-stream-string *error-output*   )
		 exit-code))))

(defmfun $system (&rest args)
  (cond ((> (length args) 1)
         ;;(apply *system-fun* args))
         ($system (format nil "~{~a ~}" args)))
        (t
         #+(or clisp ecl sbcl gcl)
	 (let ((args (remove-if #'(lambda(s) (or (string= s "-persist") (string= s ""))) (cdr ($split ($sremove "\"" (car args))))))
	       (inlabel (makelabel $inchar)))
	   ;; (unless (member (aref (car args) 0) '(#\/ #\.))
	   ;;   (push "/usr/bin/env" args))
	   (when (and (string= (car args) "/bin/sh") (string= (cadr args) "-c"))
	     (setq args (list "/bin/sh" "-c" (format nil "~{~a~^ ~}" (cddr args)))))
           (with-open-file (f #p"./tmp/run.log" :direction :output :if-exists :append :if-does-not-exist :create)
             (format f "system: args = ~{'~a' ~}~%" args))
	   (multiple-value-bind (output error code)
	       (#+(or clisp ecl sbcl) uiop/run-program:run-program #+gcl gcl-run-program
		  args :output :string :error-output :string)
	     ($put inlabel output                           '$system_output)
	     ($put inlabel (if (> (length error) 0) error)  '$system_error )
	     ($put inlabel code                             '$system_code  )
	     ))
	 ;; #-(or clisp ecl sbcl gcl)
	 ;; (merror "system is not implemented for this Lisp.")
	 ))
  '$done)

;; Epilog: yamwi.php looks for <!--END--> tag in output
(setq *maxima-epilog*   (format nil "</tbody></table></div>~%<!--END-->~%"))

;; Wrapper around `batch'
;; yamwi.php looks for <!--START--> tag in output
(defvar $original_standard_output *standard-output*)
(defvar $standard_output (make-string-output-stream))
(defvar *trusted-calculation* t
  "If T, then `MEVAL-SECURE' evaluates without checking the trusted
status of the form. If NIL, then `MEVAL-SECURE' checks the trusted
status and throws an error if the form is untrusted and contains a
prohibited symbol.")
(defun $yamwi_batch(filename &optional unique-id)
  (let* ((*maxima-quiet* t)
	 (*read-base* 10.)
	 (*trusted-calculation* nil)
	 ($values '((mlist))) ($arrays '((mlist))) ($aliases '((mlist))) ($rules '((mlist))) ($props '((mlist))) ($let_rule_packages '((mlist))) ($functions '((mlist))) ($macros '((mlist))) ($gradefs '((mlist))) ($dependencies '((mlist))) ($structures '((mlist))) ($labels '((mlist)))
	 (id (format nil "-~x" (random most-positive-fixnum)))
	 (*maxima-prolog* (format nil "<!--START-->~%<div id=\"maxima-div~a\" class=\"maxima-div\"><table id=\"maxima-output~:*~a\" class=\"maxima-output\">~%<tbody>~%<tr id='maxima-banner~:*~a' class='maxima-banner'><th colspan='7'><pre>~%" (if unique-id id ""))))
    (maxima-banner)
    (format t "</pre></th></tr>~%")
    (let ((*standard-output* $standard_output))
      (mfuncall '$batch filename))
    (setq $showtime nil) ;; don't print time taken by $YAMWI_BATCH, it messes up output
    'end_of_file
    ))
(defun $yamwi_batch_uid (filename) ($yamwi_batch filename t))

;; Sanitize output
(defmspec $yamwi_with_sanitized_output (l)
  (let* ((body (cadr l))          ;; BODY must print output to *STANDARD-OUTPUT*
	 (strm (null (caddr l)))) ;; STRM is T by default (no arg), otherwise NIL
    (format strm "~a" ($xml_sanitize (with-output-to-string (*standard-output*) (meval body))))))

;; Printer for Lisp Sexps
;; We have little protection against loops in x
(defun $%yamwi_filter_lisp(x &optional (strm t))
  (labels ((yamwi-filter-from-car (l &optional (a 'src))
	     (cond ((null l) l)
		   ((atom l) l)
		   ((and (listp l) (listp (car l)))
		    (let* ((car-l (remove-if #'(lambda(i)
						 (or  (and (atom i)
							   (eq i 'untrusted))
						      (and (listp i)
							   (some #'(lambda(e) (eq e a)) i))))
					     (car l))))
		      (append (list car-l) (mapcar #'yamwi-filter-from-car (rest l)))))
		   ((listp l)
		    (mapcar #'yamwi-filter-from-car l))
		   (t
		    l))))
    (cond ((and (listp x) (listp (car x)) (eq (caar x) 'mlabel))
	   ($%yamwi_filter_lisp (caddr x) strm))
	  (t
	   (let ((*print-circle* t))
	     (format strm "~w" (yamwi-filter-from-car x)))))))
;;
(defmvar *print* (symbol-function 'print-impl))
(defun $print (&rest l)
  (let (($display2d t) ($linel 1000.)
	(form `((mlabel simp) nil ((yamwiprint simp) ,@l))))
    (displa form)))

;; Define printers for the YAMWIPRINT
(defprop yamwiprint tex-yamwiprint tex)
(defprop yamwiprint (("\\left. ")" \\right. ") texsym)
(defun tex-yamwiprint (x l r)
  (setq l (append l (car (texsym (caar x))))
	;; car of texsym of a matchfix operator is the lead op
	r (append (list (nth 1 (texsym (caar x)))) r)
	;; cdr is the trailing op
	x (tex-list (cdr x) nil r (or (nth 2 (texsym (caar x))) "")))
  (append l x))

(defprop yamwiprint mathml-yamwiprint mathml)
(defprop yamwiprint (("<mo>[</mo>")"<mo>]</mo> ") mathmlsym)
(defun mathml-yamwiprint (x l r)
  (setq l (append l (car (mathmlsym (caar x))))
	;; car of mathmlsym of a matchfix operator is the lead op
	r (append (list (nth 1 (mathmlsym (caar x)))) r)
	;; cdr is the trailing op
	x (mathml-list (cdr x) nil r (or (nth 2 (mathmlsym (caar x))) (format nil "<mspace width=~s/>" (get-mathml-mathspace 'thickmathspace)))))
  (append l x))

(displa-def yamwiprint  dimension-match "" "")

;; KILL
(defmspec $kill (l)
  (let* ((prohibited `($all $values $arrays $aliases $rules $props
		       $let_rule_packages $functions $macros $gradefs $dependencies $structures
		       $labels $inlabels $inchar $outlabels $outchar $linelabels $linechar
		       $file_search_maxima $file_search_lisp $file_search_demo ,@$labels))
	 bad
	 (x (remove-if #'(lambda(y) (if (member y prohibited) (push y bad))) (cdr l))))
    (when bad
      (mwarning (format nil "the symbol~P ~{`~a'~^,~} ~[is~:;are~] <b style='font-weight: bold; font-size: large;'>immortal</b>! ~%Yamwi declines your `kill' request." (length bad) (mapcar #'print-invert-case (mapcar #'stripdollar bad)) (1- (length bad)))))
    ;; (format t "KILL: x=~{~a~^,~}~%l=~{~a~^,~}" x l)
    (cond ((some #'(lambda(y) (and (listp y) (eq (caar y) '$allbut))) x)
	   (mwarning "kill(allbut(...)) is not allowed in Yamwi."))
	  (t
	   (mapc #'kill1 x)
	   `((%killed_list) ,@x)))))

;;;; Secure MEVAL
;;
;; To secure Maxima's evaluator, MEVAL, we define a list
;; *PROHIBITED-SYMBOLS* and a state *TRUSTED-CALCULATION*. When
;; *TRUSTED-CALCULATION* is T, MEVAL evaluates a form without further
;; inspection. When it is NIL, MEVAL checks if the form's CAR contains
;; the symbol UNTRUSTED. If it does, then check if the CAAR is an
;; untrusted symbol or if the CAAR is an MFEXPR* or if the CAR
;; contains ARRAY. In the former case, emit an error message; in the
;; latter two cases, check if the form contains an untrusted symbol
;; and emit an error message if so.
;;
;;;;
(defun yerror (fmt-string &rest l)
  (apply #'format (append (list *error-output* fmt-string) (mapcar #'(lambda(s) (print-invert-case (stripdollar s))) l)))
  (throw 'macsyma-quit nil))

(defvar *prohibited-symbols*
  '(;; Prevent access to LISP variables/functions from Maxima
    $to_lisp
    ;; Prevent calls to a shell
    $system
    ;; Prevent strings from being evaluated
    $eval_string $eval_lisp_string
    ;; Prevent writing to filesystem
    $compfile $compile_file $translate_file
    $opena $openr $openw $write_data
    ;; Filter IO commands (from section 13.2 of Maxima manual)
    $appendfile
    $closefile $file_output_append $filename_merge
    $file_search $file_search_maxima $file_search_lisp
    $file_search_demo $file_search_usage $file_search_tests
    $file_type $file_type_lisp $file_type_maxima
    $load_pathname $loadfile
    $loadprint $pathname_directory $pathname_name
    $pathname_type $printfile $save
    $stringout $with_stdout $writefile
    ;; Prevent snooping
    $run_testsuite
    $filename_merge $file_search $file_type $directory
    $pathname_directory $pathname_name $pathname_type
    ;; Prevent package loading
    $loadfile $setup_autoload
    ;; Prevent reading files
    $read_matrix $read_lisp_array $read_maxima_array $read_hashed_array
    $read_nested_list $read_list
    ;; Prevent misc. graphics operations
    $openplot_curves $xgraph_curves $plot2d_ps $psdraw_curve $pscom
    ;; Prevent string/symbol creation
    $readbyte $readchar $readline $writebyte
    $make_string_input_stream $make_string_output_stream $get_output_stream_string
    ;; Prevent access to functions/variables in yamwi.mac
    $yamwi_display1d $yamwi_display2d $oned_display $twod_display $mathml $set_alt_display $set_prompt $reset_displays
    $%num_sentence% $%num_grafico% $mwdrawxd $Draw2d $Draw3d $Draw $mwplotxd $Plot2d $Plot3d $Scatterplot $Histogram $Barsplot $Piechart $Boxplot $Starplot $Drawdf
    $maxima_tempdir $maxima_userdir $%num_proceso% $%codigo_usuario% $%dir_sources% $%movie_muxer% $%movie_is_embedded% $%ffmpeg_binary% $%base64_cmd% $%output_mode% $%gcl%
    $standard_output $original_standard_output
    ;; not available
    $plotdf $julia $mandelbrot
    ))
(defun mheader (op)
  (declare (special $%codigo_usuario%))
  (let* ((ret  (add-lineinfo (or (safe-get op 'mheader) (ncons op))))
	 (file (cadadr ret)))
    (cond ((and (not *trusted-calculation*)
		(and (stringp file) ($ssearch ($sconcat "/tmp/" $%codigo_usuario% ".mac") file))
		(member op *prohibited-symbols*))
	   (yerror "</pre><div class='prohibited'>MHEADER: Use of the symbol <u class='prohibited'>~a</u> is prohibited in Yamwi.</div><pre class='maxima-error'>" op))
	  ((or *trusted-calculation* (null file))
	   ret)
	  (t
	   (reverse (cons 'untrusted (reverse ret)))))))

(defvar *meval-fun* (symbol-function 'meval))
(defun meval-secure (form)
  (labels ((find-prohibited (x)
	     ;; Recursively search the form `x' for a prohibited symbol.
	     ;; If one is found, then it is returned, otherwise NIL.
	     (cond (*trusted-calculation* nil)
		   ((and (atom x)
			 (member x *prohibited-symbols*)
			 x))
		   ((consp x)
		    (or (and (car x) (find-prohibited (car x)))
			(and (cdr x) (find-prohibited (cdr x)))))
		   (t nil)))
	   (prohibited-result (x)
	     ;; either x is a prohibited symbol or x is a form ((OP
	     ;; ...) * **) where OP=MSETQ MSET or $MAKELIST and *
	     ;; contains a prohibited symbol [if ** contains a
	     ;; prohibited symbol, it will get trapped by the first
	     ;; clause, eventually].
	     (when (not *trusted-calculation*)
	       (cond ((or (eq x t) (null x)) nil)
		     ((and (atom x)
			   (member x *prohibited-symbols*))
		      x)
		     ((and (listp x) (listp (car x)))
		      (cond ((member 'untrusted (car x))
			     (or
			      (car (member (caar x) *prohibited-symbols*))
			      (and (get    (caar x) 'mfexpr*)
				   ;; According to src/mtrace.lisp, lines
				   ;; 111-136, the only functions that do not
				   ;; evaluate their argument are MFEXPR*. In
				   ;; that case, we must directly scan X to find
				   ;; any prohibited symbols, rather than
				   ;; relying on recursive calls to MEVAL.
				   (find-prohibited x))))
			    ;; An ARRAY is not processed by MHEADER,
			    ;; so treat them as untrusted.
			    ((member 'array (car x))
			     (car (member (caar x) *prohibited-symbols*)))
			    (t nil)))
		     (t
		      nil))))
	   (bad-news (sym)
	     (when sym
	       (yerror "</pre><div class='prohibited'>MEVAL: Use of the symbol <u class='prohibited'>~a</u> is prohibited in Yamwi.</div><pre class='maxima-error'>" sym))))
  (unless (bad-news (prohibited-result form))
    (let ((result (funcall *meval-fun* form)))
      (unless (bad-news (prohibited-result result))
	result)))))
(defun meval (form) (meval-secure form))

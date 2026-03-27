;; Make a string-stream for *ERROR-OUTPUT*, so that we can inspect it.
(setf *error-output* (make-string-output-stream))

;; RETRIEVE is the function used to ask questions of the user.
;; We redefine it here to return print the question and then throw to the tag macsyma-quit.
;; This halts further batch-ing of the script, but allows previously computed results to be printed
;; along with the question.
(defvar *retrieve-fun* (symbol-function 'retrieve))
(defun retrieve (form &rest optional)
  (let ((msg (with-output-to-string (s)
	       (let ($display2d *alt-display1d* (*standard-output* s) *display-labels-p*)
		 (format t "<tr class='retrieve'><td></td><td><span class='retrieve'>Maxima asked: <u>")
		 (displa form)
		 (format t "</u></span></td></tr><tr class='retrieve'><td></td><td><span class='retrieve'>Questions can be answered by using <a href='https://maxima.sourceforge.io/docs/manual/Maxima_0027s-Database.html#index-assume'>assume</a> or typing `y;', `n;', `p;', `z;', `nz;' as appropriate.")))))
    (let ((result (catch 'macsyma-quit
		    (let ((*error-output* (make-string-output-stream)))
		      (apply *retrieve-fun* `(,form ,@optional))))))
      (cond ((eq result 'maxima-error)
	     (displa (list '(mtext) (list '(mtext) msg)))
	     (throw 'macsyma-quit nil))
	    (t
	     result)))))

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

;; patches to lurkmathml
;; `mfenced' is an unsupported mathml element
;; It is recommended to use `mrow' + `mo'
;; https://developer.mozilla.org/en-US/docs/Web/MathML/Reference/Element/mfenced
(defprop mlist (("<mrow><mo>[</mo>")"<mo>]</mo></mrow> ") mathmlsym)
(defprop mabs (("<mo>|</mo>")"<mo>|</mo> ") mathmlsym)
(defprop mprogn (("<mo>(</mo>") "<mo>)</mo> ") mathmlsym)

(defun mathml-matrix(x l r) ;;matrix looks like ((mmatrix)((mlist) a b) ...)
  (append l `("<mrow><mo>(</mo><mtable>")
	  (mapcan #'(lambda(y)
		      (mathml-list (cdr y) (list "<mtr><mtd>") (list "</mtd></mtr> ") "</mtd><mtd>"))
		  (cdr x))
	  '("</mtable><mo>)</mo></mrow> ") r))

(defun mathml-paren (x l r)
  (mathml x (append l '("<mrow>")) (cons "</mrow> " r) 'mparen 'mparen))

(defun mathml1 (mexplabel )
  (prog (mexp texport $gcprint)
     ;; $gcprint = nil turns gc messages off
     (cond ((null mexplabel)
	    (displa " No eqn given to MathML")
	    (return nil)))
     (setq texport *standard-output*)
     ;; go back and analyze the first arg more thoroughly now.
     ;; do a normal evaluation of the expression in macsyma
     (setq mexp (meval mexplabel))
     (cond ((member mexplabel $labels :test #'eq); leave it if it is a label
	    (setq mexplabel (aformat nil "(~a)" (stripdollar mexplabel))))
	   (t (setq mexplabel nil)));flush it otherwise

     ;; display the expression for MathML now:
     (myprinc "<math xmlns=\"http://www.w3.org/1998/Math/MathML\"> " texport)
     (mapc #'(lambda (x) (myprinc x texport))
	   ;;initially the left and right contexts are
	   ;; empty lists, and there are implicit parens
	   ;; around the whole expression
	   (mathml mexp nil nil 'mparen 'mparen))
     (cond (mexplabel
	    (format texport "<mspace width=\"verythickmathspace\"/> <mtext>~a</mtext> " mexplabel)))
     (format texport "</math>")
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
(setq *maxima-epilog*   (format nil "</table></div>~%<!--END-->~%"))

;; Wrapper around `batch'
;; yamwi.php looks for <!--START--> tag in output
(defvar $original_standard_output *standard-output*)
(defvar $standard_output (make-string-output-stream))
(defun $yamwi_batch(filename)
  (let ((*maxima-quiet* t)
	(*read-base* 10.)
	(*maxima-prolog* (format nil "<!--START-->~%<div id=\"maxima-div\"><table id=\"maxima-output\" class=\"maxima-output\">~%<tr id='maxima-banner'><td></td><td><pre>~%")))
    (maxima-banner)
    (format t "</pre></td></tr>~%")
    (let ((*standard-output* $standard_output))
      (mfuncall '$batch filename))
    'end_of_file
    ))

;; Printer for Lisp Sexps
;; We have little protection against loops in x
(defun $%yamwi_filter_lisp(x &optional (strm t))
  (labels ((circular-p (x)                 ; Adapted from CLtL1
	     (do ((n 0 (1+ n))
		  (fast x (cddr fast))     ;Fast pointer: leaps by 2
		  (slow x (cdr slow)))     ;Slow pointer: leaps by 1
		 (nil)
	       ;; If fast pointer hits the end, list is not circular
	       (when (or (endp fast) (endp (cdr fast))) (return nil))
	       ;; If fast = slow, x is circular
	       (when (and (eq fast slow) (> n 0)) (return t))))
	   (yamwi-filter (l &optional (a 'src))
	     (cond ((null l) l)
		   ((atom l) l)
		   ((and (listp l) (not (circular-p l)))
		    (let ((l (remove-if (lambda(x) (and (listp x) (not (circular-p x)) (some (lambda(e) (eq e a)) x))) l)))
		      (mapcar (lambda(i) (yamwi-filter i a)) l)))
		   (t
		    l))))
    (cond ((and (listp x) (listp (car x)) (eq (caar x) 'mlabel))
	   ($%yamwi_filter_lisp (caddr x) strm))
	  (t
	   (let ((*print-circle* t))
	     (format strm "~a" (yamwi-filter x)))))))
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

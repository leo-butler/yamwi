;; RETRIEVE is the function used to ask questions of the user.
;; We redefine it here to return print the question and then throw to the tag macsyma-quit.
;; This halts further batch-ing of the script, but allows previously computed results to be printed
;; along with the question.
(defun retrieve (form &rest optional)
  (declare (ignore optional))
  (let ((msg (with-output-to-string (s)
	       (let ($display2d *alt-display1d* (*standard-output* s) *display-labels-p*)
		 (format t "<tr class='retrieve'><td></td><td><span class='retrieve'>Maxima asked: <u>")
		 (displa form)
		 (format t "</u></span></td></tr>")))))
    (displa (list '(mtext) (list '(mtext) msg)))
    (throw 'macsyma-quit nil)))

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
(defun parse-tyi ()
  (let ((x (parse-tyi0)))
    (if x (push x *input-char-stream*))
    x))
(defun $literal_input (x)
  (declare (special *input-char-stream*))
  (let ((c-tag (cadr x)))
    ;; (format t "~%~%c-tag = ~a~%input-char-stream = ~{~a~}~%" c-tag (reverse *input-char-stream*))
    (setf (get 'vinput c-tag) (reverse *input-char-stream*)
	  *input-char-stream*  '())
    (coerce (get 'vinput c-tag) 'string)))

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

(defun mathml1 (mexplabel &optional filename ) ;; mexplabel, and optional filename
  (prog (mexp  texport $gcprint ccol x y)
     ;; $gcprint = nil turns gc messages off
     (setq ccol 1)
     (cond ((null mexplabel)
	    (displa " No eqn given to MathML")
	    (return nil)))
     ;; collect the file-name, if any, and open a port if needed
     (setq texport (cond((null filename) *standard-output* ); t= output to terminal
			(t
			 (open (string (stripdollar filename))
			       :direction :output
			       :if-exists :append
			       :if-does-not-exist :create))))
     ;; go back and analyze the first arg more thoroughly now.
     ;; do a normal evaluation of the expression in macsyma
     (setq mexp (meval mexplabel))
     (cond ((member mexplabel $labels :test #'eq); leave it if it is a label
	    (setq mexplabel (intern (format nil "(~a)" (stripdollar mexplabel)))))
	   (t (setq mexplabel nil)));flush it otherwise

     ;; maybe it is a function?
     (cond((symbolp (setq x mexp)) ;;exclude strings, numbers
	   (setq x ($verbify x))
	   (cond ((setq y (mget x 'mexpr))
		  (setq mexp (list '(mdefine) (cons (list x) (cdadr y)) (caddr y))))
		 ((setq y (mget x 'mmacro))
		  (setq mexp (list '(mdefmacro) (cons (list x) (cdadr y)) (caddr y))))
		 ((setq y (mget x 'aexpr))
		  (setq mexp (list '(mdefine) (cons (list x 'array) (cdadr y)) (caddr y)))))))

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
     (cond(filename(terpri texport); and drain port if not terminal
		   (close texport)))
     (return mexplabel)))

;; asdf
(require 'asdf      #+clisp #P"/usr/lib/clisp-2.49.95+/asdf/asdf.lisp")
;; cl-base64
;; (require 'cl-base64 #+clisp #p"/usr/share/common-lisp/source/cl-base64/cl-base64.asd")
;; (asdf:oos 'asdf:compile-op '#:cl-base64)
;; (asdf:oos 'asdf:load-op    '#:cl-base64)

;; over-write $system
;; we assume args is a single string

(defvar *system-fun* (symbol-function 'system-impl))

(defmfun $system (&rest args)
  (cond ((> (length args) 1)
         ;;(apply *system-fun* args))
         ($system (format nil "~{~a ~}" args)))
        (t
         #+gcl
	 (let ((output (si::fp-output-stream (si:run-process (first args) (rest args)))))
           (loop for c = (read-char-no-hang output nil)
                 while c do
		   (format t "~c" c)))
         #+(or clisp ecl sbcl)
	 (let ((args (remove-if #'(lambda(s) (or (string= s "-persist") (string= s ""))) (cdr ($split ($sremove "\"" (car args))))))
	       (inlabel (makelabel $inchar)))
	   ;; (unless (member (aref (car args) 0) '(#\/ #\.))
	   ;;   (push "/usr/bin/env" args))
	   (when (and (string= (car args) "/bin/sh") (string= (cadr args) "-c"))
	     (setq args (list "/bin/sh" "-c" (format nil "~{~a~^ ~}" (cddr args)))))
           (with-open-file (f #p"./tmp/run.log" :direction :output :if-exists :append :if-does-not-exist :create)
             (format f "system: args = ~{'~a' ~}~%" args))
	   (multiple-value-bind (output error code)
	     (uiop/run-program:run-program args :output :string :error-output :string)
	     ($put inlabel output '$system_output)
	     ($put inlabel error  '$system_error )
	     ($put inlabel code   '$system_code  )
	     ))
	 ;; #-(or clisp ecl sbcl gcl)
	 ;; (merror "system is not implemented for this Lisp.")
	 ))
  '$done)

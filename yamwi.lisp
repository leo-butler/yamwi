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
(defvar $input_char_stream '())
(defun parse-tyi ()
  (let ((x (parse-tyi0)))
    (if x (push x $input_char_stream))
    x))
(defun $literal_input (x)
  (let ((c-tag (cadr x)))
    (setf (get 'vinput c-tag) (reverse $input_char_stream)
	  $input_char_stream  '())
    (coerce (get 'vinput c-tag) 'string)))


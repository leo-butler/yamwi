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
(setf (get '|$$| 'mheader) '(displayinput))

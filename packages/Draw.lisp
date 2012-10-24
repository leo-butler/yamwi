; Do not remove this package. It's necessary when the user
; wants to load package draw by hand.

(when (null ($get '$draw '$version)) (simplify ($load "draw.lisp")))


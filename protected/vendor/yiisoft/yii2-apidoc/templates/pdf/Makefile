

pdf:
	# improve list spacing
	sed -i 's/\\begin{itemize}/\\begin{itemize}[noitemsep,nolistsep]/' guide.tex
	# run pdflatex twice to generate TOC correctly
	pdflatex -halt-on-error main.tex
	pdflatex -halt-on-error main.tex
	mv main.pdf guide.pdf

clean:
	-rm -f *.aux *.out *.log *.toc

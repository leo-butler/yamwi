document.getElementById("resoudre5").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "linsolve([x + y = 5, 3*x - y = 1], [x, y]);";
  } else {
    textarea.value += "\nlinsolve([x + y = 5, 3*x - y = 1], [x, y]);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
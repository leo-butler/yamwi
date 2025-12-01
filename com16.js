document.getElementById("resoudre7").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "subst(a + b, x, x^2);";
  } else {
    textarea.value += "\nsubst(a + b, x, x^2);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
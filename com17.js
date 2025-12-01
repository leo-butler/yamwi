document.getElementById("derive1").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "diff(x^3 + 2*x, x);";
  } else {
    textarea.value += "\ndiff(x^3 + 2*x, x);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
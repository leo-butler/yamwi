document.getElementById("ana4").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "partfrac((x^3+5*x-5)/(x^2-1),x);";
  } else {
    textarea.value += "\npartfrac((x^3+5*x-5)/(x^2-1),x);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
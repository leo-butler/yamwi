document.getElementById("resoudre4").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "allroots(x^3 - 1);";
  } else {
    textarea.value += "\nallroots(x^3 - 1);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
document.getElementById("resoudre3").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "realroots(x^3 - x - 1);";
  } else {
    textarea.value += "\nrealroots(x^3 - x - 1);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
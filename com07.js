document.getElementById("simplifier3").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "radcan(sqrt(8)+log(16));";
  } else {
    textarea.value += "\nradcan(sqrt(8)+log(16));";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
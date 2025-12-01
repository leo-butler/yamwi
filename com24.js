document.getElementById("ana2").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "sum(1/i^2, i, 1, inf), simpsum;";
  } else {
    textarea.value += "\nsum(1/i^2, i, 1, inf), simpsum;";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
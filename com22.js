document.getElementById("derive6").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "romberg(sin(x)+x^2, x, 0, %pi);";
  } else {
    textarea.value += "\nromberg(sin(x)+x^2, x, 0, %pi);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
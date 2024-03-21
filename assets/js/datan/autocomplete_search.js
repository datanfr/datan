document.getElementById("search").addEventListener("input", function() {
  let query = this.value;
  if (query.length > 0) {
    fetch(`search?q=${query}`)
      .then(response => response.json())
      .then(data => {
        let resultsDiv = document.getElementById("search-results");
        resultsDiv.innerHTML = "";
        if (data.length > 0) {
          document.getElementById("search-bloc").style.borderBottomLeftRadius = "0px";
          document.getElementById("search-bloc").style.borderBottomRightRadius = "0px";
        } else {
          document.getElementById("search-bloc").style.borderRadius = "8px";
        }
        data.forEach(result => {
          let div = document.createElement("div");
          div.textContent = result.text + " - " + result.source;
          resultsDiv.appendChild(div);
        });
      });
  } else {
    document.getElementById("search-results").innerHTML = "";
    document.getElementById("search-bloc").style.borderRadius = "8px";
  }
});

function debounce(func, delay) {
  let debounceTimer;
  return function() {
    const context = this;
    const args = arguments;
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => func.apply(context, args), delay);
  };
}

function searchQuery() {
  let query = this.value;
  if (query.length > 0) {
    fetch(`search_api?q=${query}`)
      .then(response => response.json())
      .then(data => {
        document.getElementById("search-results-bloc").style.display = "block";
        let resultsDiv = document.getElementById("search-results-list");
        resultsDiv.innerHTML = "";
        if (data.length > 0) {
          document.getElementById("search-bloc").style.borderBottomLeftRadius = "0px";
          document.getElementById("search-bloc").style.borderBottomRightRadius = "0px";
        } else {
          document.getElementById("search-bloc").style.borderRadius = "8px";
        }
        data.forEach(result => {
          let div = document.createElement("a");
          div.textContent = result.text;
          div.className = result.source + " no-decoration";
          div.href = "https://datan.fr/" + result.url;
          let description = document.createElement("div");
          description.className = "description";
          description.textContent = result.description.replace(/<\/?[^>]+(>|$)/g, "");
          div.appendChild(description);
          resultsDiv.appendChild(div);
        });
      });
      document.getElementById("more-results-link").href = "search/" + query;
  } else {
    document.getElementById("search-results-list").innerHTML = "";
    document.getElementById("search-bloc").style.borderRadius = "8px";
    document.getElementById("search-results-bloc").style.display = "none";
  }
}

document.getElementById("search").addEventListener("input", debounce(searchQuery, 250)); // 250 ms de délai


// Placeholder
let input = document.querySelector("input");
let slider = document.querySelector(".slider");
let slider_box = document.querySelector(".slider_box");
let placeholder = document.querySelector(".placeholder");

let list = ["un député", "une ville", "un groupe politique", "un vote"];
let word = 0;
let intervals = "";
text_animation();
setintervals();
slider_box.style.height = slider.clientHeight + "px";

input.onfocus = function(){
  placeholder.style.display = "none";
  clearInterval(intervals);
}
input.onblur = function(){
  if (input.value == "") {
    placeholder.style.display = "flex";
    word = 0;
    text_animation();
    setintervals();
  }
}

function setintervals(){
  intervals = setInterval(() => {
    text_animation();
  }, 2500);
}

function text_animation(){
  word++;
  slider.innerHTML = list[word - 1];
  slider.style.opacity = "1";
  slider.style.left = "0px";

  setTimeout(() => {
    slider.style.opacity = "0";
    slider.style.left = "-5px";
  }, 2000);
  if(list.length == word) {
    word = 0;
  }
}
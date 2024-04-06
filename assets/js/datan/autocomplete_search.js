var currentFocus = -1;

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
        currentFocus = -1;
        document.getElementById("more-results-link").classList.remove("active");
        if (data.length > 0) {
          document.getElementById("search-bloc").style.borderBottomLeftRadius = "0px";
          document.getElementById("search-bloc").style.borderBottomRightRadius = "0px";
        } else {
          document.getElementById("search-bloc").style.borderRadius = "8px";
        }
        data.forEach(result => {
          let div = document.createElement("a");
          div.innerHTML = result.text;
          div.className = result.source + " no-decoration";
          div.href = get_base_url() + "/" + result.url;
          console.log(result);
          resultsDiv.appendChild(div);
        });
      });
      document.getElementById("more-results-link").href = "recherche/" + query;
  } else {
    closeDropdown();
  }
}

document.getElementById("search").addEventListener("input", debounce(searchQuery, 250)); // 250 ms de délai

// Close the result list if the user clicks outside of it
window.addEventListener("click", function(event) {
  if (!event.target.matches('#search')) {
    closeDropdown();
    clearInput();
    startPlaceholderAnimation();
  }
});

// Close the result list when pressing escape
window.addEventListener("keydown", function(event) {
  if (event.key === "Escape") {
    closeDropdown();
    clearInput();
    startPlaceholderAnimation();
  }
});

// System when navigating through the results with keyboard
document.getElementById("search").addEventListener("keydown", function(e) {
  var links = document.getElementById("search-results-bloc");
  if (links) links = links.getElementsByTagName("a");
  var key = e.key;
  if (key === "ArrowDown" || key === "ArrowUp") {
      e.preventDefault();
    if (key === "ArrowDown") {
      currentFocus++;
      addActive(links);
    } else if (key === "ArrowUp") {
      currentFocus--;
      addActive(links);
    }
  } else if (key === "Enter") {
    e.preventDefault();
    if (currentFocus === -1) {
      window.location.href = 'recherche/' + $('#search').val();
    } else {
      links[currentFocus].click();
    }
  }
});

function addActive(x) {
  if (!x) return false;
  removeActive(x);
  if (currentFocus >= x.length) currentFocus = 0;
  if (currentFocus < 0) currentFocus = (x.length - 1);
  x[currentFocus].classList.add("active");
}

function removeActive(x) {
  for (var i = 0; i < x.length; i++) {
    x[i].classList.remove("active");
  }
}

function closeDropdown(){
  document.getElementById("search-results-list").innerHTML = "";
  document.getElementById("search-bloc").style.borderRadius = "8px";
  document.getElementById("search-results-bloc").style.display = "none";
  document.getElementById("search").blur();
}

// Placeholder
let input = document.querySelector("input");
let slider = document.querySelector(".slider");
let slider_box = document.querySelector(".slider_box");
let placeholder = document.querySelector(".placeholder");

let list = ["un député", "une ville", "un groupe", "un vote"];
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
    startPlaceholderAnimation();
  }
}

function startPlaceholderAnimation(){
  placeholder.style.display = "flex";
  word = 0;
  text_animation();
  setintervals();
}

function clearInput() {
  var inputElement = document.getElementById("search");
  inputElement.value = "";
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

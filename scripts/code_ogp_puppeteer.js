const puppeteer = require('puppeteer');
const fetch = require('node-fetch');

let json = "https://datan.fr/assets/data/deputes_json.json";
let settings = { method: "Get" };

// TAKE ALL MPS
fetch(json, settings).then(res => res.json()).then((json) => {
  json = json.slice(0, 1);
  json.forEach((item, i) => {
  });
});

async function run () {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await fetch(json, settings).then(res => res.json()).then(async (json) => {
      json = json.slice(0, 1);
      console.log(json);
      json.forEach(async (item, i) => {
        console.log('try');
        let url = 'http://localhost/datan/scripts/code_photos_ogp_export.php?uid=' + item['id'];

        await page.goto(url, { waitUntil: 'networkidle2', timeout: 0 });
        page.waitFor(3000);
        page.close();
      });
    });
    browser.close();
}

run();

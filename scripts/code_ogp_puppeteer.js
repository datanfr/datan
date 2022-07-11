const puppeteer = require('puppeteer');
const fetch = require('node-fetch');

let jsonUrl = "https://datan.fr/assets/data/deputes_json.json";
let settings = { method: "Get" };

async function run() {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();
  let json = await fetch(jsonUrl, settings).then(res => res.json())
  //let jsonSlice = json.slice(0, 1);
  for (const item of json) {
    let url = 'http://localhost/datan/scripts/code_photos_ogp_export.php?uid=' + item['id'];
    console.log(url);

    await page.goto(url, { waitUntil: 'networkidle2', timeout: 0 });
    await page.waitFor(20);
  }
  await browser.close();
}

run();

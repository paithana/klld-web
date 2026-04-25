async function scrapeAll() {
  let allReviews = [];
  let baseUrl = "https://www.tripadvisor.com/Attraction_Review-g297914-d1960808-Reviews";
  let suffix = "-Khao_Lak_Land_Discovery-Khao_Lak_Takua_Pa_Phang_Nga_Province.html";
  
  for (let offset = 0; offset <= 1250; offset += 10) {
      let url = offset === 0 ? `${baseUrl}${suffix}` : `${baseUrl}-or${offset}${suffix}`;
      console.log(`Fetching ${url} ...`);
      
      try {
          let res = await fetch(url);
          if (!res.ok) {
              console.log(`Failed to fetch offset ${offset}, status: ${res.status}`);
              break;
          }
          let text = await res.text();
          let parser = new DOMParser();
          let doc = parser.parseFromString(text, 'text/html');
          
          let cards = doc.querySelectorAll('div[data-automation="reviewCard"]');
          if (cards.length === 0) {
              console.log("No more cards or blocked at offset " + offset);
              break;
          }
          
          cards.forEach(card => {
              const id = card.id || Math.random().toString(36).substr(2, 9);
              
              const nameEl = card.querySelector('span > a[href*="/Profile/"]') || card.querySelector('span.biGQs');
              const name = nameEl ? nameEl.innerText.trim() : "TripAdvisor Traveler";
              
              const titleEl = card.querySelector('div[data-automation="reviewTitle"]');
              const title = titleEl ? titleEl.innerText.trim() : "";
              
              const textEl = card.querySelector('span.yCeTE') || card.querySelector('span.ySdfQ') || card.querySelector('.yCeTE');
              const textContent = textEl ? textEl.innerText.trim() : "";
              
              const bubbles = card.querySelector('svg[aria-label*="of 5 bubbles"]') || card.querySelector('title');
              let rating = 5;
              if (bubbles) {
                  const aria = bubbles.getAttribute('aria-label');
                  if (aria && aria.match(/(\d+(\.\d+)?) of 5 bubbles/)) {
                      rating = parseFloat(aria.match(/(\d+(\.\d+)?) of 5 bubbles/)[1]);
                  } else if (bubbles.tagName && bubbles.tagName.toLowerCase() === 'title' && bubbles.innerHTML.match(/(\d+(\.\d+)?) of 5 bubbles/)) {
                      rating = parseFloat(bubbles.innerHTML.match(/(\d+(\.\d+)?) of 5 bubbles/)[1]);
                  } else if (bubbles.className && typeof bubbles.className === 'string' && bubbles.className.match(/bubble_(\d+)/)) {
                      rating = parseInt(bubbles.className.match(/bubble_(\d+)/)[1]) / 10;
                  }
              }

              const dateTextEl = card.querySelector('div.biGQs.pZpkB.ncFvB') || card.querySelector('.ncFvB');
              const dateText = dateTextEl ? dateTextEl.innerText : "";
              const date = dateText.replace("Written ", "").trim();
              
              const avatarEl = card.querySelector('img.cyS_u') || card.querySelector('img[src*="avatar"]');
              const avatar = avatarEl ? avatarEl.src : null;

              const photos = [];
              card.querySelectorAll('img').forEach(img => {
                  if (img.src && img.src.includes('/media/photo-') && !img.src.includes('avatar')) {
                      photos.push(img.src);
                  }
              });

              let reviewOf = "";
              const links = card.querySelectorAll('a');
              links.forEach(link => {
                  if (link.innerText.includes("Review of")) {
                      reviewOf = link.innerText.replace("Review of:", "").replace("Review of", "").trim();
                  }
              });

              allReviews.push({ id, n: name, ti: title, co: textContent, rt: rating, dt: date, av: avatar, ro: reviewOf, ph: photos });
          });
          
          const delay = Math.floor(Math.random() * 2000) + 1000;
          await new Promise(r => setTimeout(r, delay));
      } catch (e) {
          console.error("Error at offset " + offset, e);
          break;
      }
  }
  
  let jsonString = JSON.stringify(allReviews, null, 2);
  console.log("Scraped " + allReviews.length + " reviews.");
  
  // Create a blob and trigger download
  let blob = new Blob([jsonString], {type: "application/json"});
  let a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = "manual_import.json";
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  
  return allReviews.length;
}

window.scrapePromise = scrapeAll();

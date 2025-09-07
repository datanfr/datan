(async function () {
  try {
    const res = await fetch("/campaign/current_active_campaigns", {
      method: "GET",
    });
    if (res.ok) {
      const campaigns = await res.json();
      if (campaigns.length) {
        const campaignSection = document.getElementById("campaign");
        const campaignParagraph = document.getElementById("campaign-paragraph");
        const paragraph = campaigns[0].text;
        campaignSection.classList.toggle("d-none");
        campaignSection.classList.toggle("d-block");
        campaignParagraph.innerHTML = paragraph;
      }
    }
  } catch (error) {
    console.error(error);
  }
})();

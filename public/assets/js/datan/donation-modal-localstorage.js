/**
 * Donation Modal Tracker
 * Utilise localStorage au lieu des cookies
 * Compte les pages visitées et affiche un modal après X visites
 */

(function() {
  const THRESHOLD = 10; // Nombre de pages avant d'afficher le modal
  const ONE_WEEK_MS = 7 * 24 * 60 * 60 * 1000; // Une semaine en millisecondes

  /**
   * Récupère les données de localStorage
   */
  function getStorageData(key) {
    try {
      const data = localStorage.getItem(key);
      return data ? JSON.parse(data) : null;
    } catch (e) {
      console.error('Erreur localStorage:', e);
      return null;
    }
  }

  /**
   * Sauvegarde les données dans localStorage
   */
  function setStorageData(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
      return true;
    } catch (e) {
      console.error('Erreur localStorage:', e);
      return false;
    }
  }

  /**
   * Initialise le tracking des pages visitées
   */
  function trackPageVisit() {
    // Récupère le mois actuel au format YYYY-M
    const now = new Date();
    const currentMonth = now.getFullYear() + '-' + (now.getMonth() + 1);

    // Récupère ou initialise les données
    let data = getStorageData('datan_monthly');

    // Réinitialise si on est dans un nouveau mois
    if (!data || data.month !== currentMonth) {
      data = { month: currentMonth, count: 0 };
    }

    // Incrémente le compteur
    data.count++;
    setStorageData('datan_monthly', data);

    // Met à jour le compteur dans le footer (si l'élément existe)
    const el = document.getElementById('monthly-pages-visited-count');
    if (el) {
      el.textContent = data.count;
    }

    // Vérifie si le modal doit s'afficher
    checkAndShowDonationModal(data.count);
  }

  /**
   * Vérifie si le modal doit s'afficher
   */
  function checkAndShowDonationModal(visitCount) {
    // Si l'utilisateur n'a pas atteint le seuil, ne rien faire
    if (visitCount < THRESHOLD) {
      return;
    }

    // Récupère la dernière fois où le modal a été affiché
    const lastShown = getStorageData('datan_modal_shown');
    const now = Date.now();

    // Si le modal a été affiché il y a moins d'une semaine, ne rien faire
    if (lastShown && (now - lastShown) < ONE_WEEK_MS) {
      return;
    }

    // Sauvegarde le timestamp actuel
    setStorageData('datan_modal_shown', now);

    // Affiche le modal une fois que le DOM est prêt
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function() {
        showDonationModal(visitCount);
      });
    } else {
      showDonationModal(visitCount);
    }
  }

  /**
   * Affiche le modal de donation
   */
  function showDonationModal(visitCount) {
    // Met à jour le texte du modal
    const modalCountElement = document.getElementById('modal-page-count');
    if (modalCountElement) {
      modalCountElement.textContent = visitCount;
    }

    // Affiche le modal (si jQuery et Bootstrap sont disponibles)
    if (typeof jQuery !== 'undefined' && jQuery('#donationModal').length) {
      jQuery('#donationModal').modal('show');
    }
  }

  // Lance le tracking quand le DOM est prêt
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', trackPageVisit);
  } else {
    trackPageVisit();
  }
})();
// assets/entityList.js

export function setupEntityListSorting() {
  const grid = document.querySelector('.list-grid');
  if (!grid) return;
  const rows = Array.from(grid.querySelectorAll('.list-row'));
  const headerCells = Array.from(grid.querySelector('.list-header').children);

  const getCellValue = (row, idx) => row.children[idx].innerText.trim();
  const compare = (type, a, b) => {
    if (type === 'date') {
      const parse = v => {
        const [d, m, y] = v.split('/');
        return new Date(`${y}-${m}-${d}`);
      };
      return parse(a) - parse(b);
    }
    if (type === 'genre') {
      if (a === b) return 0;
      if (a.toLowerCase().startsWith('f')) return -1;
      if (b.toLowerCase().startsWith('f')) return 1;
      return a.localeCompare(b);
    }
    return a.localeCompare(b);
  };

  grid.querySelectorAll('.sort-btn').forEach((btn, idx) => {
    btn.addEventListener('click', function() {
      const fieldIdx = headerCells.indexOf(btn.parentNode);
      const type = btn.dataset.type;
      let asc = btn.classList.toggle('asc');
      btn.classList.toggle('desc', !asc);

      // Récupère toutes les lignes à trier (ignore les lignes vides)
      const sortableRows = Array.from(grid.querySelectorAll('.list-row')).filter(
        row => row.children.length === headerCells.length
      );

      sortableRows.sort((a, b) => {
        let vA = getCellValue(a, fieldIdx);
        let vB = getCellValue(b, fieldIdx);
        let cmp = compare(type, vA, vB);
        return asc ? cmp : -cmp;
      });
      // Replace les lignes dans l'ordre
      sortableRows.forEach(row => grid.appendChild(row));
    });
  });

  // Fonction séparée pour gérer les clics sur les lignes
  const setupRowClicks = () => {
    rows.forEach(row => {
      const href = row.dataset.href;
      if (href) {
        // Supprime l'ancien écouteur s'il existe
        row.removeEventListener('click', rowClickHandler);
        // Ajoute le nouvel écouteur
        row.addEventListener('click', rowClickHandler);
        row.style.cursor = 'pointer';
      }
    });
  };

  // Gestionnaire d'événement de clic séparé
  const rowClickHandler = function(e) {
    if (!e.target.classList.contains('sort-btn')) {
      window.location.href = this.dataset.href;
    }
  };

  // Filtrage par inscription
  const subscriptionToggle = document.querySelector('[data-filter="subscription"]');
  if (subscriptionToggle) {
    const filterRows = () => {
      const showUnsubscribed = subscriptionToggle.checked;
      
      rows.forEach(row => {
        const unsubscriptionDate = row.dataset.unsubscriptionDate;
        const isUnsubscribed = unsubscriptionDate ? new Date(unsubscriptionDate) < new Date() : false;
        
        if (showUnsubscribed) {
          row.style.display = isUnsubscribed ? 'grid' : 'none';
        } else {
          row.style.display = !isUnsubscribed ? 'grid' : 'none';
        }
      });

      // Réapplique les écouteurs de clic après le filtrage
      setupRowClicks();
    };

    subscriptionToggle.addEventListener('change', filterRows);
    // Initial filter and click setup
    filterRows();
  }

  // Initial click setup
  setupRowClicks();
}
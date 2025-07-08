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

  // Rendre chaque ligne cliquable
  rows.forEach(row => {
    const href = row.dataset.href;
    if (href) {
      row.addEventListener('click', () => {
        window.location = href;
      });
      row.style.cursor = 'pointer';
    }
  });
} 